<?php

namespace App\Policies;

use App\Models\ContactMessage;
use App\Models\User;

class ContactMessagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('cms.manage-other-modules');
    }

    public function view(User $user, ContactMessage $contactMessage): bool
    {
        return $user->can('cms.manage-other-modules');
    }

    public function update(User $user, ContactMessage $contactMessage): bool
    {
        return $user->can('cms.manage-other-modules');
    }
}
