<?php

namespace Tests\Feature;

use App\Mail\PasswordResetOtp;
use App\Models\PasswordResetCode;
use App\Models\User;
use App\Services\MailConfigurationService;
use App\Services\PasswordResetOtpService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PasswordResetOtpTest extends TestCase
{
    use RefreshDatabase;

    private const GENERIC_MESSAGE = 'Si la información proporcionada corresponde a una cuenta registrada, recibirás instrucciones para continuar.';

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => 'smtp.example.test',
            'mail.mailers.smtp.port' => 587,
            'mail.from.address' => 'noreply@example.test',
        ]);
        app()->forgetInstance(MailConfigurationService::class);
        Mail::fake();
    }

    public function test_request_is_generic_for_known_and_unknown_addresses_and_otp_is_only_hashed(): void
    {
        $user = User::factory()->create(['email' => 'known-reset@example.test']);

        $this->post(route('password.request.send'), ['email' => 'unknown-reset@example.test'])
            ->assertRedirect(route('password.otp.form'))
            ->assertSessionHas('status', self::GENERIC_MESSAGE);
        $this->assertDatabaseCount('password_reset_codes', 0);

        $this->post(route('password.request.send'), ['email' => $user->email])
            ->assertRedirect(route('password.otp.form'))
            ->assertSessionHas('status', self::GENERIC_MESSAGE);
        $this->assertDatabaseHas('audit_logs', ['event' => 'password_reset_requested', 'user_id' => null]);

        $resetCode = PasswordResetCode::query()->firstOrFail();
        $this->assertSame(6, strlen($this->sentCodeFor($user)));
        $this->assertNotSame($this->sentCodeFor($user), $resetCode->code_hash);
        $this->assertTrue(Hash::check($this->sentCodeFor($user), $resetCode->code_hash));
        $this->assertSame(0, $resetCode->attempts);
        $this->assertNull($resetCode->used_at);
        $this->assertTrue($resetCode->expires_at->between(now()->addMinutes(9), now()->addMinutes(10)));
    }

    public function test_invalid_code_increments_attempts_and_is_rejected_after_limit(): void
    {
        $user = User::factory()->create(['email' => 'attempts-reset@example.test']);
        $this->requestCode($user);
        $resetCode = PasswordResetCode::query()->firstOrFail();
        $validCode = $this->sentCodeFor($user);
        $wrongCode = $validCode === '000000' ? '000001' : '000000';

        foreach (range(1, 5) as $attempt) {
            $this->post(route('password.otp.verify'), ['code' => $wrongCode])
                ->assertRedirect(route('password.otp.form'))
                ->assertSessionHasErrors('code');
        }

        $resetCode = PasswordResetCode::query()->firstOrFail();
        $this->assertSame(5, $resetCode->attempts);

        $this->post(route('password.otp.verify'), ['code' => $validCode])
            ->assertRedirect(route('password.otp.form'))
            ->assertSessionHasErrors('code');
    }

    public function test_expired_code_is_rejected(): void
    {
        $user = User::factory()->create(['email' => 'expired-reset@example.test']);
        $this->requestCode($user);
        PasswordResetCode::query()->firstOrFail()->update(['expires_at' => now()->subSecond()]);

        $this->from(route('password.otp.form'))->post(route('password.otp.verify'), ['code' => $this->sentCodeFor($user)])
            ->assertRedirect(route('password.otp.form'))
            ->assertSessionHasErrors('code');
    }

    public function test_code_can_only_reset_password_once_and_dispatches_password_reset_event(): void
    {
        Event::fake([PasswordReset::class]);
        $user = User::factory()->create([
            'email' => 'complete-reset@example.test',
            'password' => 'OldPassword12345',
        ]);
        $oldRememberToken = $user->remember_token;
        config(['session.driver' => 'database']);
        DB::table('sessions')->insert([
            'id' => 'previous-authenticated-session',
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'test',
            'payload' => '',
            'last_activity' => now()->timestamp,
        ]);
        $this->requestCode($user);
        $resetCode = PasswordResetCode::query()->firstOrFail();

        $this->from(route('password.otp.form'))->post(route('password.otp.verify'), ['code' => $this->sentCodeFor($user)])
            ->assertRedirect(route('password.reset.form'));

        $this->get(route('password.reset.form'))->assertOk()->assertSee('Crea una nueva contraseña');
        $this->post(route('password.reset.update'), [
            'password' => 'NewSecurePassword123',
            'password_confirmation' => 'NewSecurePassword123',
        ])->assertRedirect(route('login'))
            ->assertSessionHas('status', 'La contraseña se actualizó. Ya puedes iniciar sesión.');

        $this->assertTrue(Hash::check('NewSecurePassword123', $user->fresh()->password));
        $this->assertFalse(Hash::check('OldPassword12345', $user->fresh()->password));
        $this->assertNotSame($oldRememberToken, $user->fresh()->remember_token);
        $this->assertNotNull(PasswordResetCode::query()->firstOrFail()->fresh()->used_at);
        $this->assertFalse(app(PasswordResetOtpService::class)->resetPassword(
            $user->email,
            (int) $resetCode->getKey(),
            'AnotherSecurePassword456',
        ));
        $this->assertDatabaseMissing('sessions', ['id' => 'previous-authenticated-session']);
        $this->assertDatabaseHas('audit_logs', ['event' => 'password_changed', 'user_id' => $user->id]);
        Event::assertDispatched(PasswordReset::class, fn (PasswordReset $event): bool => $event->user->is($user));

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'NewSecurePassword123',
        ])->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($user);

        $this->post(route('password.otp.verify'), ['code' => $this->sentCodeFor($user)])
            ->assertRedirect(route('home'));
    }

    public function test_resend_respects_cooldown_then_invalidates_previous_code(): void
    {
        $user = User::factory()->create(['email' => 'resend-reset@example.test']);
        $this->requestCode($user);
        $original = PasswordResetCode::query()->firstOrFail();

        $this->post(route('password.otp.resend'))
            ->assertRedirect(route('password.otp.form'))
            ->assertSessionHas('status', self::GENERIC_MESSAGE);
        $this->assertDatabaseCount('password_reset_codes', 1);

        $this->travel(61)->seconds();
        $this->post(route('password.otp.resend'))
            ->assertRedirect(route('password.otp.form'))
            ->assertSessionHas('status', self::GENERIC_MESSAGE);

        $this->assertDatabaseCount('password_reset_codes', 2);
        $this->assertNotNull($original->fresh()->used_at);
        Mail::assertSent(PasswordResetOtp::class, 2);
    }

    public function test_request_validation_applies(): void
    {
        $this->from(route('password.request'))->post(route('password.request.send'), ['email' => 'invalid'])
            ->assertRedirect(route('password.request'))
            ->assertSessionHasErrors('email');
    }

    public function test_request_ip_rate_limit_applies(): void
    {
        foreach (range(1, 5) as $attempt) {
            $this->post(route('password.request.send'), ['email' => 'rate-limit-reset@example.test'])
                ->assertRedirect(route('password.otp.form'));
        }

        $this->post(route('password.request.send'), ['email' => 'rate-limit-reset@example.test'])
            ->assertTooManyRequests();
    }

    public function test_password_requires_confirmation_and_strength(): void
    {
        $user = User::factory()->create(['email' => 'weak-password-reset@example.test']);
        $this->requestCode($user);
        $this->post(route('password.otp.verify'), ['code' => $this->sentCodeFor($user)]);

        $this->from(route('password.reset.form'))->post(route('password.reset.update'), [
            'password' => 'weak',
            'password_confirmation' => 'different',
        ])->assertRedirect(route('password.reset.form'))
            ->assertSessionHasErrors('password');
    }

    private function requestCode(User $user): void
    {
        $this->post(route('password.request.send'), ['email' => $user->email])
            ->assertRedirect(route('password.otp.form'))
            ->assertSessionHas('status', self::GENERIC_MESSAGE);
    }

    private function sentCodeFor(User $user): string
    {
        $code = null;
        Mail::assertSent(PasswordResetOtp::class, function (PasswordResetOtp $mail) use (&$code, $user): bool {
            if ($mail->hasTo($user->email)) {
                $code = $mail->code;
            }

            return $mail->hasTo($user->email);
        });

        return (string) $code;
    }
}
