<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('users.view');
    }

    public function create(User $user): bool
    {
        return $user->can('users.create');
    }

    public function update(User $user, User $managedUser): bool
    {
        return $user->can('users.update') && $managedUser->exists
            && ($managedUser->role !== 'super_admin' || $user->isSuperAdmin());
    }

    public function changeStatus(User $user, User $managedUser): bool
    {
        return $this->update($user, $managedUser);
    }

    public function delete(User $user, User $managedUser): bool
    {
        return $user->can('users.delete') && $managedUser->exists
            && ($managedUser->role !== 'super_admin' || $user->isSuperAdmin());
    }
}
