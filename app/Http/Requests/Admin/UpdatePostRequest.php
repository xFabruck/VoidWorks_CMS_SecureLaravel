<?php

namespace App\Http\Requests\Admin;

use App\Models\Post;
use Illuminate\Validation\Rule;

class UpdatePostRequest extends PostFormRequest
{
    public function authorize(): bool
    {
        $post = $this->route('post');

        return $post instanceof Post && ($this->user()?->can('update', $post) ?? false);
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $post = $this->route('post');

        return $this->postRules(Rule::unique('posts', 'slug')->ignore($post?->getKey()));
    }
}
