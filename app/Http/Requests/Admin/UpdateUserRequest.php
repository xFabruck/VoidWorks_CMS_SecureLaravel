<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $managedUser = $this->route('user');

        $user = $this->user();

        return $managedUser instanceof User
            && ($user?->can('update', $managedUser) ?? false)
            && ($this->input('role') !== 'super_admin' || $user->isSuperAdmin());
    }

    public function rules(): array
    {
        $managedUser = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($managedUser?->getKey())],
            'password' => ['nullable', 'confirmed', Password::min(12)->letters()->numbers()],
            'role' => ['required', Rule::in(['super_admin', 'admin', 'editor', 'author'])],
            'status' => ['required', Rule::in(['active', 'suspended', 'blocked'])],
        ];
    }
}
