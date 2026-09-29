<?php

namespace App\Policies;

use App\Models\Banner;
use App\Models\User;

class BannerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('banners.view');
    }

    public function view(User $user, Banner $banner): bool
    {
        return $user->can('banners.view') && $this->owns($user, $banner);
    }

    public function create(User $user): bool
    {
        return $user->can('banners.create');
    }

    public function update(User $user, Banner $banner): bool
    {
        return $user->can('banners.update') && $this->owns($user, $banner);
    }

    public function delete(User $user, Banner $banner): bool
    {
        return $user->can('banners.delete') && $this->owns($user, $banner);
    }

    private function owns(User $user, Banner $banner): bool
    {
        return $banner->created_by !== null && (int) $banner->created_by === (int) $user->getKey();
    }
}
