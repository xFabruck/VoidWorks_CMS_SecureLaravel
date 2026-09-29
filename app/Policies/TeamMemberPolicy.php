<?php

namespace App\Policies;

use App\Models\TeamMember;
use App\Models\User;

class TeamMemberPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('cms.manage-other-modules');
    }

    public function create(User $user): bool
    {
        return $user->can('cms.manage-other-modules');
    }

    public function view(User $user, TeamMember $teamMember): bool
    {
        return $user->can('cms.manage-other-modules');
    }

    public function update(User $user, TeamMember $teamMember): bool
    {
        return $user->can('cms.manage-other-modules');
    }

    public function delete(User $user, TeamMember $teamMember): bool
    {
        return $user->can('cms.manage-other-modules');
    }
}
