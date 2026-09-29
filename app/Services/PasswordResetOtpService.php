<?php

namespace App\Services;

use App\Mail\PasswordResetOtp;
use App\Models\PasswordResetCode;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class PasswordResetOtpService
{
    public const MAX_ATTEMPTS = 5;

    public const RESEND_COOLDOWN_SECONDS = 60;

    private const EMAIL_REQUEST_LIMIT = 3;

    private const EMAIL_REQUEST_DECAY_SECONDS = 600;

    public function __construct(
        private readonly MailConfigurationService $mailConfiguration,
        private readonly AuditLogger $audit,
    ) {}

    public function requestCode(string $email): void
    {
        $email = mb_strtolower(trim($email));
        $rateLimitKey = 'password-reset-otp:'.hash('sha256', $email);

        if (RateLimiter::tooManyAttempts($rateLimitKey, self::EMAIL_REQUEST_LIMIT)) {
            return;
        }

        RateLimiter::hit($rateLimitKey, self::EMAIL_REQUEST_DECAY_SECONDS);

        $user = User::query()->where('email', $email)->where('status', 'active')->first();

        if (! $user) {
            return;
        }

        $latestCode = PasswordResetCode::query()
            ->where('user_id', $user->getKey())
            ->latest('id')
            ->first();

        if ($latestCode && $latestCode->created_at->gt(now()->subSeconds(self::RESEND_COOLDOWN_SECONDS))) {
            return;
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        DB::transaction(function () use ($user, $code): void {
            PasswordResetCode::query()
                ->where('user_id', $user->getKey())
                ->whereNull('used_at')
                ->update(['used_at' => now(), 'updated_at' => now()]);

            PasswordResetCode::query()->create([
                'user_id' => $user->getKey(),
                'code_hash' => Hash::make($code),
                'expires_at' => now()->addMinutes(10),
                'attempts' => 0,
            ]);
        });

        if (! $this->mailConfiguration->configureForSending()) {
            return;
        }

        try {
            Mail::to($user->email)->send(new PasswordResetOtp($code));
        } catch (Throwable) {
            Log::warning('Password reset OTP email delivery failed.');
        }
    }

    public function verify(string $email, string $code): ?int
    {
        $user = $this->activeUser($email);

        if (! $user) {
            return null;
        }

        return DB::transaction(function () use ($user, $code): ?int {
            $resetCode = PasswordResetCode::query()
                ->where('user_id', $user->getKey())
                ->whereNull('used_at')
                ->latest('id')
                ->lockForUpdate()
                ->first();

            if (! $this->isUsable($resetCode)) {
                return null;
            }

            if (! Hash::check($code, $resetCode->code_hash)) {
                $resetCode->attempts++;
                $resetCode->save();

                return null;
            }

            return (int) $resetCode->getKey();
        });
    }

    public function resetPassword(string $email, int $codeId, string $password): bool
    {
        $user = null;

        $completed = DB::transaction(function () use ($email, $codeId, $password, &$user): bool {
            $candidate = $this->activeUser($email, lock: true);

            if (! $candidate) {
                return false;
            }

            $resetCode = PasswordResetCode::query()
                ->whereKey($codeId)
                ->where('user_id', $candidate->getKey())
                ->whereNull('used_at')
                ->lockForUpdate()
                ->first();

            if (! $this->isUsable($resetCode)) {
                return false;
            }

            $candidate->password = Hash::make($password);
            $candidate->remember_token = Str::random(60);
            $candidate->save();

            PasswordResetCode::query()
                ->where('user_id', $candidate->getKey())
                ->whereNull('used_at')
                ->update(['used_at' => now(), 'updated_at' => now()]);

            $user = $candidate->refresh();

            return true;
        });

        if (! $completed || ! $user) {
            return false;
        }

        if (config('session.driver') === 'database') {
            $sessionTable = (string) config('session.table', 'sessions');

            if (Schema::hasTable($sessionTable)) {
                DB::table($sessionTable)->where('user_id', $user->getKey())->delete();
            }
        }

        event(new PasswordReset($user));
        $this->audit->record('password_changed', $user, actorId: (int) $user->getKey());
        Log::info('User password reset completed through OTP recovery.', ['user_id' => $user->getKey()]);

        return true;
    }

    private function activeUser(string $email, bool $lock = false): ?User
    {
        $query = User::query()->where('email', mb_strtolower(trim($email)))->where('status', 'active');

        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->first();
    }

    private function isUsable(?PasswordResetCode $resetCode): bool
    {
        return $resetCode !== null
            && $resetCode->attempts < self::MAX_ATTEMPTS
            && $resetCode->expires_at->isFuture()
            && $resetCode->used_at === null;
    }
}
