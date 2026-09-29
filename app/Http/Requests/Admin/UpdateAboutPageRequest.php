<?php

namespace App\Http\Requests\Admin;

use App\Services\AboutPageManager;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAboutPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        $aboutPage = app(AboutPageManager::class)->forAdmin();

        return $this->user()?->can('update', $aboutPage) ?? false;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:160'],
            'subtitle' => ['nullable', 'string', 'max:220'],
            'content' => ['required', 'string', 'max:30000'],
            'primary_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120', 'dimensions:max_width=6000,max_height=6000'],
            'mission' => ['required', 'string', 'max:12000'],
            'vision' => ['required', 'string', 'max:12000'],
            'values' => ['required', 'string', 'max:12000'],
            'history' => ['required', 'string', 'max:30000'],
            'secondary_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120', 'dimensions:max_width=6000,max_height=6000'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'title' => 'título',
            'subtitle' => 'subtítulo',
            'content' => 'contenido',
            'primary_image' => 'imagen principal',
            'mission' => 'misión',
            'vision' => 'visión',
            'values' => 'valores',
            'history' => 'historia',
            'secondary_image' => 'imagen secundaria',
            'is_active' => 'estado',
        ];
    }
}
