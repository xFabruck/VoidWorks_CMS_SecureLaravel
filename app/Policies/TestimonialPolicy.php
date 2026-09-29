<?php

namespace App\Policies;

use App\Models\Testimonial;
use App\Models\User;

class TestimonialPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('cms.manage-other-modules');
    }

    public function create(User $user): bool
    {
        return $user->can('cms.manage-other-modules');
    }

    public function update(User $user, Testimonial $testimonial): bool
    {
        return $user->can('cms.manage-other-modules');
    }

    public function delete(User $user, Testimonial $testimonial): bool
    {
        return $user->can('cms.manage-other-modules');
    }
}
