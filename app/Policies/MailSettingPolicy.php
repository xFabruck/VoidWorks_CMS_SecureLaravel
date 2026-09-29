<?php

namespace App\Policies;

use App\Models\MailSetting;
use App\Models\User;

class MailSettingPolicy
{
    public function view(User $user, MailSetting $mailSetting): bool
    {
        return $user->can('settings.view');
    }

    public function update(User $user, MailSetting $mailSetting): bool
    {
        return $user->can('settings.update');
    }
}
