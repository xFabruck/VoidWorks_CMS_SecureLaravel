<?php

namespace App\Http\Requests\Admin;

use App\Services\MailConfigurationService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MailSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', app(MailConfigurationService::class)->current()) ?? false;
    }

    public function rules(): array
    {
        if ($this->routeIs('admin.settings.mail.test')) {
            return ['test_recipient' => ['required', 'email', 'max:254']];
        }

        return [
            'mailer' => ['required', Rule::in(['smtp'])],
            'host' => ['nullable', 'required_if:is_active,1', 'string', 'max:253', 'regex:/^[A-Za-z0-9.-]+$/'],
            'port' => ['nullable', 'required_if:is_active,1', 'integer', 'between:1,65535'],
            'encryption' => ['nullable', Rule::in(['tls', 'ssl'])],
            'username' => ['nullable', 'string', 'max:254'],
            'password' => ['nullable', 'string', 'max:4096'],
            'from_address' => ['nullable', 'required_if:is_active,1', 'email', 'max:254'],
            'from_name' => ['nullable', 'required_if:is_active,1', 'string', 'max:180'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'mailer' => 'transportador de correo',
            'host' => 'servidor SMTP',
            'port' => 'puerto SMTP',
            'encryption' => 'cifrado SMTP',
            'username' => 'usuario SMTP',
            'password' => 'contraseña SMTP',
            'from_address' => 'correo remitente',
            'from_name' => 'nombre remitente',
            'test_recipient' => 'correo destinatario de prueba',
        ];
    }
}
