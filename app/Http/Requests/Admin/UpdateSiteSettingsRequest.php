<?php

namespace App\Http\Requests\Admin;

use App\Services\SiteSettingsManager;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSiteSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', app(SiteSettingsManager::class)->current()) ?? false;
    }

    public function rules(): array
    {
        return [
            'site_name' => ['required', 'string', 'min:2', 'max:180'],
            'logo' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'mimetypes:image/jpeg,image/png,image/webp', 'max:5120', 'dimensions:max_width=6000,max_height=6000'],
            'favicon' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'mimetypes:image/jpeg,image/png,image/webp', 'max:2048', 'dimensions:max_width=1024,max_height=1024'],
            'remove_logo' => ['required', 'boolean'],
            'remove_favicon' => ['required', 'boolean'],
            'contact_email' => ['nullable', 'email', 'max:254'],
            'phone' => ['nullable', 'string', 'max:60', 'regex:/^[0-9+().\-\s]+$/'],
            'address' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:5000'],
            'footer_text' => ['nullable', 'string', 'max:2000'],
            'locale' => ['required', Rule::in(['es', 'en'])],
            'presentation_timezone' => ['required', 'timezone'],
        ];
    }

    public function attributes(): array
    {
        return [
            'site_name' => 'nombre del sitio',
            'logo' => 'logo',
            'favicon' => 'favicon',
            'contact_email' => 'correo de contacto',
            'phone' => 'teléfono',
            'address' => 'dirección',
            'description' => 'descripción',
            'footer_text' => 'texto del pie de página',
            'locale' => 'idioma',
            'presentation_timezone' => 'zona horaria de presentación',
        ];
    }
}
