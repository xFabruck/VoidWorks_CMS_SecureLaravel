<?php

namespace App\Http\Requests\Admin;

use App\Models\Service;
use Illuminate\Validation\Rule;

class UpdateServiceRequest extends ServiceFormRequest
{
    public function authorize(): bool
    {
        $service = $this->route('service');

        return $service instanceof Service && ($this->user()?->can('update', $service) ?? false);
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $service = $this->route('service');

        return $this->serviceRules('nullable', Rule::unique('services', 'slug')->ignore($service?->getKey()));
    }
}
