<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $managedUser = $this->route('user');

        return $managedUser instanceof User && ($this->user()?->can('changeStatus', $managedUser) ?? false);
    }

    public function rules(): array
    {
        return ['status' => ['required', Rule::in(['active', 'suspended', 'blocked'])]];
    }
}
