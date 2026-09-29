<?php

namespace App\Services;

use App\Mail\MailConfigurationTestMessage;
use App\Models\MailSetting;
use App\Models\MailSettingAudit;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class MailConfigurationService
{
    /** @param array<string, mixed> $infrastructureFallback */
    public function __construct(private readonly array $infrastructureFallback) {}

    public function current(): MailSetting
    {
        return MailSetting::query()->first() ?? new MailSetting([
            'mailer' => 'smtp',
            'encryption' => null,
            'is_active' => false,
        ]);
    }

    public function hasStoredPassword(?MailSetting $setting = null): bool
    {
        return filled(($setting ?? $this->current())->password);
    }

    /** @param array<string, mixed> $data */
    public function update(array $data, int $userId): MailSetting
    {
        return DB::transaction(function () use ($data, $userId): MailSetting {
            $setting = MailSetting::query()->lockForUpdate()->first() ?? new MailSetting;
            $setting->mailer = $data['mailer'];
            $setting->host = $data['host'] ?? null;
            $setting->port = $data['port'] ?? null;
            $setting->encryption = $data['encryption'] ?? null;
            $setting->username = $data['username'] ?? null;
            $setting->from_address = $data['from_address'] ?? null;
            $setting->from_name = $data['from_name'] ?? null;
            $setting->is_active = (bool) $data['is_active'];

            if (filled($data['password'] ?? null)) {
                $setting->password = Crypt::encryptString($data['password']);
            }

            $setting->save();

            MailSettingAudit::query()->create([
                'mail_setting_id' => $setting->getKey(),
                'user_id' => $userId,
                'action' => MailSettingAudit::UPDATED,
            ]);

            return $setting->refresh();
        });
    }

    /** Applies database SMTP settings or restores the private infrastructure fallback. */
    public function configureForSending(bool $useSavedSettingsWhenInactive = false): bool
    {
        Config::set('mail', $this->infrastructureFallback);
        $this->purgeMailer('smtp');

        $settingQuery = MailSetting::query();

        if (! $useSavedSettingsWhenInactive) {
            $settingQuery->where('is_active', true);
        }

        $setting = $settingQuery->first();

        if ($setting !== null) {
            if ($setting->mailer !== 'smtp' || blank($setting->host) || blank($setting->from_address)) {
                return false;
            }

            try {
                $password = filled($setting->password) ? Crypt::decryptString($setting->password) : null;
            } catch (Throwable) {
                Log::warning('Stored mail configuration could not be decrypted.');

                return false;
            }

            $smtp = array_replace($this->infrastructureFallback['mailers']['smtp'] ?? [], [
                'transport' => 'smtp',
                'scheme' => $setting->encryption === 'ssl' ? 'smtps' : 'smtp',
                'url' => null,
                'host' => $setting->host,
                'port' => $setting->port,
                'username' => $setting->username,
                'password' => $password,
            ]);

            Config::set('mail.default', 'smtp');
            Config::set('mail.mailers.smtp', $smtp);
            Config::set('mail.from', [
                'address' => $setting->from_address,
                'name' => $setting->from_name,
            ]);
        }

        $this->purgeMailer('smtp');

        return config('mail.default') === 'smtp' && filled(config('mail.mailers.smtp.host'));
    }

    public function sendTest(string $recipient, int $userId): bool
    {
        $setting = MailSetting::query()->first();

        if (! $this->configureForSending(useSavedSettingsWhenInactive: true)) {
            $this->recordAudit($setting, $userId, MailSettingAudit::TEST_FAILED);

            return false;
        }

        try {
            Mail::to($recipient)->send(new MailConfigurationTestMessage);
            $this->recordAudit($setting, $userId, MailSettingAudit::TEST_SENT);

            return true;
        } catch (Throwable) {
            $this->recordAudit($setting, $userId, MailSettingAudit::TEST_FAILED);
            Log::warning('Mail configuration test could not be sent.', ['user_id' => $userId]);

            return false;
        }
    }

    private function recordAudit(?MailSetting $setting, int $userId, string $action): void
    {
        MailSettingAudit::query()->create([
            'mail_setting_id' => $setting?->getKey(),
            'user_id' => $userId,
            'action' => $action,
        ]);
    }

    private function purgeMailer(string $name): void
    {
        app('mail.manager')->purge($name);
    }
}
