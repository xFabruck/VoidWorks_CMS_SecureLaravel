<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return ($user?->can('create', User::class) ?? false)
            && ($this->input('role') !== 'super_admin' || $user->isSuperAdmin());
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'confirmed', Password::min(12)->letters()->numbers()],
            'role' => ['required', Rule::in(['super_admin', 'admin', 'editor', 'author'])],
            'status' => ['required', Rule::in(['active', 'suspended', 'blocked'])],
        ];
    }
}
