<?php

namespace App\Http\Requests\Admin;

use App\Models\Post;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class PostFormRequest extends FormRequest
{
    /** @return array<string, array<int, mixed>> */
    protected function postRules(mixed $slugRule): array
    {
        $scheduled = $this->input('status') === 'scheduled';

        return [
            'title' => ['required', 'string', 'max:200'],
            'slug' => ['nullable', 'string', 'max:180', 'alpha_dash:ascii', $slugRule],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string', 'max:50000'],
            'featured_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120', 'dimensions:max_width=6000,max_height=6000'],
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')->whereNull('deleted_at')],
            'status' => ['required', Rule::in(Post::STATUSES)],
            'published_at' => $scheduled
                ? ['required', 'date', 'after:now']
                : ['nullable', 'date'],
            'seo_title' => ['nullable', 'string', 'max:180'],
            'meta_description' => ['nullable', 'string', 'max:300'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'title' => 'título', 'slug' => 'slug', 'excerpt' => 'resumen', 'content' => 'contenido',
            'featured_image' => 'imagen destacada', 'category_id' => 'categoría',
            'status' => 'estado', 'published_at' => 'fecha de publicación',
            'seo_title' => 'título SEO', 'meta_description' => 'meta descripción',
        ];
    }
}
