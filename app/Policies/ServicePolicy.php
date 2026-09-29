<?php

namespace App\Policies;

use App\Models\Service;
use App\Models\User;

class ServicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('services.view');
    }

    public function create(User $user): bool
    {
        return $user->can('services.create');
    }

    public function update(User $user, Service $service): bool
    {
        return $user->can('services.update') && $service->created_by !== null && (int) $service->created_by === (int) $user->getKey();
    }

    public function delete(User $user, Service $service): bool
    {
        return $user->can('services.delete') && $service->created_by !== null && (int) $service->created_by === (int) $user->getKey();
    }
}
