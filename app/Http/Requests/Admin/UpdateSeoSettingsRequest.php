<?php

namespace App\Http\Requests\Admin;

use App\Services\SeoSettingsManager;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateSeoSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', app(SeoSettingsManager::class)->current()) ?? false;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'site_title' => ['required', 'string', 'min:2', 'max:180'],
            'default_meta_description' => ['required', 'string', 'min:10', 'max:300'],
            'default_og_image' => ['nullable', 'string', 'url:https', 'max:2048'],
            'robots_index' => ['required', 'boolean'],
            'robots_follow' => ['required', 'boolean'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $image = $this->input('default_og_image');

            if (! is_string($image) || $image === '' || $validator->errors()->has('default_og_image')) {
                return;
            }

            $host = parse_url($image, PHP_URL_HOST);
            $user = parse_url($image, PHP_URL_USER);
            $password = parse_url($image, PHP_URL_PASS);
            $extension = strtolower(pathinfo((string) parse_url($image, PHP_URL_PATH), PATHINFO_EXTENSION));

            if (! is_string($host) || $host === '' || $user !== null || $password !== null) {
                $validator->errors()->add('default_og_image', 'La imagen debe ser una URL HTTPS pública sin credenciales.');
            } elseif (! in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
                $validator->errors()->add('default_og_image', 'La imagen Open Graph debe terminar en JPG, JPEG, PNG o WebP.');
            }
        }];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'site_title' => 'título del sitio',
            'default_meta_description' => 'meta descripción general',
            'default_og_image' => 'imagen Open Graph',
            'robots_index' => 'indexación',
            'robots_follow' => 'seguimiento de enlaces',
        ];
    }
}
