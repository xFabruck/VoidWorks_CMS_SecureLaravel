<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

abstract class ServiceFormRequest extends FormRequest
{
    /** @return array<string, array<int, mixed>> */
    protected function serviceRules(string $imageRequirement, mixed $slugRule): array
    {
        return [
            'name' => ['required', 'string', 'max:160'],
            'slug' => ['nullable', 'string', 'max:180', 'alpha_dash:ascii', $slugRule],
            'short_description' => ['required', 'string', 'max:300'],
            'description' => ['required', 'string', 'max:20000'],
            'icon' => ['nullable', 'string', 'max:64', 'alpha_dash:ascii'],
            'image' => [$imageRequirement, 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120', 'dimensions:max_width=6000,max_height=6000'],
            'button_text' => ['nullable', 'required_with:button_url', 'string', 'max:100'],
            'button_url' => ['nullable', 'required_with:button_text', 'url:http,https', 'max:2048'],
            'position' => ['required', 'integer', 'min:0', 'max:65535'],
            'is_active' => ['required', 'boolean'],
            'seo_title' => ['nullable', 'string', 'max:180'],
            'meta_description' => ['nullable', 'string', 'max:300'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'name' => 'nombre', 'slug' => 'slug', 'short_description' => 'descripción corta',
            'description' => 'descripción', 'icon' => 'icono', 'image' => 'imagen',
            'button_text' => 'texto del botón', 'button_url' => 'URL del botón',
            'position' => 'orden', 'is_active' => 'estado', 'seo_title' => 'título SEO',
            'meta_description' => 'meta descripción',
        ];
    }
}
