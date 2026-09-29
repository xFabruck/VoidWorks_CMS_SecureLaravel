<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('posts.view');
    }

    public function create(User $user): bool
    {
        return $user->can('posts.create');
    }

    public function view(User $user, Post $post): bool
    {
        return $user->can('posts.view') && $this->canAccessPost($user, $post);
    }

    public function update(User $user, Post $post): bool
    {
        return $user->can('posts.update') && $this->canAccessPost($user, $post);
    }

    public function publish(User $user, Post $post): bool
    {
        return $user->can('posts.publish') && $this->canAccessPost($user, $post);
    }

    public function unpublish(User $user, Post $post): bool
    {
        return $user->can('posts.publish') && $this->canAccessPost($user, $post);
    }

    public function archive(User $user, Post $post): bool
    {
        return $user->can('posts.publish') && $this->canAccessPost($user, $post);
    }

    public function delete(User $user, Post $post): bool
    {
        return $user->can('posts.delete') && $this->canAccessPost($user, $post);
    }

    private function canAccessPost(User $user, Post $post): bool
    {
        return $post->author_id !== null && (int) $post->author_id === (int) $user->getKey();
    }
}
