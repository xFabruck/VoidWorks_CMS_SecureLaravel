<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

abstract class TestimonialFormRequest extends FormRequest
{
    /** @return array<string, array<int, mixed>> */
    protected function testimonialRules(string $photoRequirement): array
    {
        return [
            'name' => ['required', 'string', 'max:160'],
            'position_or_company' => ['required', 'string', 'max:180'],
            'testimonial' => ['required', 'string', 'max:5000'],
            'photo' => [$photoRequirement, 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120', 'dimensions:max_width=6000,max_height=6000'],
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'position' => ['required', 'integer', 'min:0', 'max:65535'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'name' => 'nombre', 'position_or_company' => 'cargo o empresa', 'testimonial' => 'testimonio',
            'photo' => 'fotografía', 'rating' => 'calificación', 'position' => 'orden', 'is_active' => 'estado',
        ];
    }
}
