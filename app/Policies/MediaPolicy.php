<?php

namespace App\Policies;

use App\Models\Media;
use App\Models\User;

class MediaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('media.view');
    }

    public function view(User $user, Media $media): bool
    {
        return $user->can('media.view');
    }

    public function create(User $user): bool
    {
        return $user->can('media.upload');
    }

    public function delete(User $user, Media $media): bool
    {
        return $user->can('media.delete') && $media->uploaded_by !== null && (int) $media->uploaded_by === (int) $user->getKey();
    }
}
