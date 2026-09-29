<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

abstract class TeamMemberFormRequest extends FormRequest
{
    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:160'],
            'position' => ['required', 'string', 'max:160'],
            'biography' => ['nullable', 'string', 'max:5000'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120', 'dimensions:max_width=6000,max_height=6000'],
            'email' => ['nullable', 'email:rfc', 'max:254'],
            'phone' => ['nullable', 'string', 'max:32', 'regex:/^[0-9+(). xX-]{5,32}$/'],
            'linkedin_url' => ['nullable', 'url:https', 'max:2048'],
            'position_order' => ['required', 'integer', 'min:0', 'max:65535'],
            'is_active' => ['required', 'boolean'],
            'show_biography' => ['required', 'boolean'],
            'show_photo' => ['required', 'boolean'],
            'show_email' => ['required', 'boolean'],
            'show_phone' => ['required', 'boolean'],
            'show_linkedin_url' => ['required', 'boolean'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $url = $this->input('linkedin_url');
            if (! is_string($url) || $url === '') {
                return;
            }

            $parts = parse_url($url);
            $host = is_array($parts) ? strtolower($parts['host'] ?? '') : '';
            $isLinkedIn = $host === 'linkedin.com' || str_ends_with($host, '.linkedin.com');
            $safePort = ! isset($parts['port']) || $parts['port'] === 443;

            if (! $isLinkedIn || isset($parts['user']) || isset($parts['pass']) || ! $safePort) {
                $validator->errors()->add('linkedin_url', 'La URL debe pertenecer al dominio oficial de LinkedIn.');
            }
        }];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'name' => 'nombre', 'position' => 'cargo', 'biography' => 'biografía', 'photo' => 'fotografía',
            'email' => 'correo electrónico', 'phone' => 'teléfono', 'linkedin_url' => 'perfil de LinkedIn',
            'position_order' => 'orden', 'is_active' => 'estado',
        ];
    }
}
