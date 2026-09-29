<?php

namespace App\Policies;

use App\Models\SeoSetting;
use App\Models\User;

class SeoSettingPolicy
{
    public function update(User $user, SeoSetting $seoSetting): bool
    {
        return $user->can('settings.update');
    }
}
