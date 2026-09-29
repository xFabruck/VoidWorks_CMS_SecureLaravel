<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

class CategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('cms.manage-other-modules');
    }

    public function create(User $user): bool
    {
        return $user->can('cms.manage-other-modules');
    }

    public function update(User $user, Category $category): bool
    {
        return $user->can('cms.manage-other-modules') && $category->exists && ! $category->trashed();
    }

    public function delete(User $user, Category $category): bool
    {
        return $user->can('cms.manage-other-modules') && $category->exists && ! $category->trashed();
    }
}
