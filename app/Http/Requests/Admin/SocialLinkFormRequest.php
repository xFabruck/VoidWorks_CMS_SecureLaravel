<?php

namespace App\Http\Requests\Admin;

use App\Models\SocialLink;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

abstract class SocialLinkFormRequest extends FormRequest
{
    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'network' => ['required', 'string', Rule::in(SocialLink::NETWORKS)],
            'url' => ['required', 'string', 'url:https', 'max:2048'],
            'position' => ['required', 'integer', 'min:0', 'max:65535'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $network = $this->input('network');
            $url = $this->input('url');

            if (! is_string($network) || ! is_string($url) || $validator->errors()->has('url')) {
                return;
            }

            $host = strtolower(rtrim((string) parse_url($url, PHP_URL_HOST), '.'));
            $user = parse_url($url, PHP_URL_USER);
            $password = parse_url($url, PHP_URL_PASS);

            if ($host === '' || $user !== null || $password !== null) {
                $validator->errors()->add('url', 'La URL debe tener un dominio público y no incluir credenciales.');

                return;
            }

            $domains = match ($network) {
                'facebook' => ['facebook.com', 'fb.com'],
                'instagram' => ['instagram.com'],
                'linkedin' => ['linkedin.com'],
                'youtube' => ['youtube.com', 'youtu.be'],
                'tiktok' => ['tiktok.com'],
                'x' => ['x.com', 'twitter.com'],
                'whatsapp' => ['whatsapp.com', 'wa.me'],
                default => [],
            };

            if ($domains !== [] && ! $this->matchesAllowedDomain($host, $domains)) {
                $validator->errors()->add('url', 'La URL debe corresponder al dominio oficial de la red seleccionada.');
            }
        }];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return ['network' => 'red social', 'url' => 'URL', 'position' => 'orden', 'is_active' => 'estado'];
    }

    /** @param array<int, string> $domains */
    private function matchesAllowedDomain(string $host, array $domains): bool
    {
        foreach ($domains as $domain) {
            if ($host === $domain || str_ends_with($host, '.'.$domain)) {
                return true;
            }
        }

        return false;
    }
}
