<?php

namespace App\Policies;

use App\Models\SocialLink;
use App\Models\User;

class SocialLinkPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('settings.view');
    }

    public function create(User $user): bool
    {
        return $user->can('settings.update');
    }

    public function update(User $user, SocialLink $socialLink): bool
    {
        return $user->can('settings.update');
    }

    public function delete(User $user, SocialLink $socialLink): bool
    {
        return $user->can('settings.update');
    }
}
