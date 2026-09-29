<?php

namespace App\Policies;

use App\Models\AboutPage;
use App\Models\User;

class AboutPagePolicy
{
    public function update(User $user, AboutPage $aboutPage): bool
    {
        return $user->can('cms.manage-other-modules') && $aboutPage->singleton_key === 'main';
    }
}
