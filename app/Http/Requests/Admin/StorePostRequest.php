<?php

namespace App\Http\Requests\Admin;

use App\Models\Post;
use Illuminate\Validation\Rule;

class StorePostRequest extends PostFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Post::class) ?? false;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return $this->postRules(Rule::unique('posts', 'slug'));
    }
}
