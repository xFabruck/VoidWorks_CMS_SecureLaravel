<?php

namespace Tests\Feature;

use App\Mail\MailConfigurationTestMessage;
use App\Models\MailSetting;
use App\Models\MailSettingAudit;
use App\Models\User;
use App\Services\MailConfigurationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class MailSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_mail_settings_require_settings_permission(): void
    {
        $this->get(route('admin.settings.mail.edit'))->assertRedirect(route('login'));

        $this->actingAs(User::factory()->create(['role' => 'editor']))
            ->get(route('admin.settings.mail.edit'))->assertForbidden();
    }

    public function test_saved_password_is_encrypted_hidden_and_never_rendered(): void
    {
        $secret = 'smtp-secret-that-must-not-render';
        $setting = $this->createSetting(['password' => Crypt::encryptString($secret)]);

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('admin.settings.mail.edit'))
            ->assertOk()
            ->assertSee('••••••••••••')
            ->assertDontSee($secret)
            ->assertDontSee($setting->password);

        $this->assertArrayNotHasKey('password', $setting->toArray());
        $this->assertSame($secret, Crypt::decryptString($setting->getRawOriginal('password')));
    }

    public function test_update_encrypts_new_password_and_blank_password_preserves_existing_secret(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);
        $secret = 'smtp-password-private';

        $this->put(route('admin.settings.mail.update'), $this->settingsData([
            'password' => $secret,
            'is_active' => '1',
        ]))->assertRedirect(route('admin.settings.mail.edit'))->assertSessionHas('status');

        $setting = MailSetting::query()->firstOrFail();
        $ciphertext = $setting->getRawOriginal('password');
        $this->assertNotSame($secret, $ciphertext);
        $this->assertSame($secret, Crypt::decryptString($ciphertext));
        $this->assertDatabaseHas('mail_setting_audits', [
            'user_id' => $admin->id,
            'mail_setting_id' => $setting->id,
            'action' => MailSettingAudit::UPDATED,
        ]);

        $this->put(route('admin.settings.mail.update'), $this->settingsData(['password' => '']))
            ->assertRedirect(route('admin.settings.mail.edit'));

        $this->assertSame($ciphertext, $setting->fresh()->getRawOriginal('password'));
        $this->assertSame($secret, Crypt::decryptString($setting->fresh()->getRawOriginal('password')));
    }

    public function test_database_smtp_configuration_is_loaded_dynamically(): void
    {
        $this->createSetting([
            'host' => 'smtp.database.test',
            'port' => 465,
            'encryption' => 'ssl',
            'password' => Crypt::encryptString('private-secret'),
            'is_active' => true,
        ]);

        $this->assertTrue(app(MailConfigurationService::class)->configureForSending());
        $this->assertSame('smtp', config('mail.default'));
        $this->assertSame('smtp.database.test', config('mail.mailers.smtp.host'));
        $this->assertSame('smtps', config('mail.mailers.smtp.scheme'));
        $this->assertSame('private-secret', config('mail.mailers.smtp.password'));
        $this->assertSame('mailer@example.test', config('mail.from.address'));
    }

    public function test_test_email_can_use_inactive_saved_configuration_and_audits_only_action(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->createSetting(['is_active' => false, 'password' => Crypt::encryptString('never-log-this-secret')]);
        Mail::fake();

        $this->actingAs($admin)->post(route('admin.settings.mail.test'), [
            'test_recipient' => 'delivery@example.test',
        ])->assertRedirect(route('admin.settings.mail.edit'))
            ->assertSessionHas('status', 'Correo de prueba enviado correctamente.')
            ->assertDontSee('never-log-this-secret');

        Mail::assertSent(MailConfigurationTestMessage::class, fn (MailConfigurationTestMessage $mail): bool => $mail->hasTo('delivery@example.test'));
        $this->assertDatabaseHas('mail_setting_audits', [
            'user_id' => $admin->id,
            'action' => MailSettingAudit::TEST_SENT,
        ]);
        $this->assertDatabaseMissing('mail_setting_audits', ['action' => 'never-log-this-secret']);
    }

    public function test_unavailable_fallback_returns_only_a_safe_error_and_records_failure(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        config(['mail.default' => 'log']);

        $this->actingAs($admin)->followingRedirects()->post(route('admin.settings.mail.test'), [
            'test_recipient' => 'delivery@example.test',
        ])->assertOk()
            ->assertSee('No fue posible enviar el correo de prueba. Revisa la configuración o el servicio de correo.')
            ->assertDontSee('local_domain')
            ->assertDontSee('smtp.example.test');

        $this->assertDatabaseHas('mail_setting_audits', [
            'user_id' => $admin->id,
            'action' => MailSettingAudit::TEST_FAILED,
        ]);
    }

    public function test_invalid_smtp_host_and_recipient_are_rejected(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $this->from(route('admin.settings.mail.edit'))
            ->put(route('admin.settings.mail.update'), $this->settingsData(['host' => 'smtp.example.test/path']))
            ->assertRedirect(route('admin.settings.mail.edit'))->assertSessionHasErrors('host');

        $this->from(route('admin.settings.mail.edit'))
            ->post(route('admin.settings.mail.test'), ['test_recipient' => 'not-an-email'])
            ->assertRedirect(route('admin.settings.mail.edit'))->assertSessionHasErrors('test_recipient');
    }

    private function createSetting(array $overrides = []): MailSetting
    {
        return MailSetting::query()->create(array_replace([
            'mailer' => 'smtp',
            'host' => 'smtp.example.test',
            'port' => 587,
            'encryption' => 'tls',
            'username' => 'smtp-user',
            'password' => null,
            'from_address' => 'mailer@example.test',
            'from_name' => 'CMS Test',
            'is_active' => true,
        ], $overrides));
    }

    /** @return array<string, mixed> */
    private function settingsData(array $overrides = []): array
    {
        return array_replace([
            'mailer' => 'smtp',
            'host' => 'smtp.example.test',
            'port' => '587',
            'encryption' => 'tls',
            'username' => 'smtp-user',
            'password' => '',
            'from_address' => 'mailer@example.test',
            'from_name' => 'CMS Test',
            'is_active' => '0',
        ], $overrides);
    }
}
