<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

abstract class BannerFormRequest extends FormRequest
{
    /** @return array<string, array<int, string>> */
    protected function bannerRules(string $imageRequirement): array
    {
        return [
            'title' => ['required', 'string', 'max:160'],
            'subtitle' => ['nullable', 'string', 'max:220'],
            'image' => [$imageRequirement, 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120', 'dimensions:max_width=6000,max_height=6000'],
            'image_alt' => ['required', 'string', 'max:255'],
            'button_text' => ['nullable', 'required_with:button_url', 'string', 'max:100'],
            'button_url' => ['nullable', 'required_with:button_text', 'url:http,https', 'max:2048'],
            'position' => ['required', 'integer', 'min:0', 'max:65535'],
            'is_active' => ['required', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'title' => 'título',
            'subtitle' => 'subtítulo',
            'image' => 'imagen',
            'image_alt' => 'texto alternativo de la imagen',
            'button_text' => 'texto del botón',
            'button_url' => 'URL del botón',
            'position' => 'orden',
            'starts_at' => 'fecha de inicio',
            'ends_at' => 'fecha de fin',
            'is_active' => 'estado',
        ];
    }
}
