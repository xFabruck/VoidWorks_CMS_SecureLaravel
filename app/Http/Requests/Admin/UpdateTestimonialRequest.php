<?php

namespace App\Http\Requests\Admin;

class UpdateTestimonialRequest extends TestimonialFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('testimonial')) ?? false;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return $this->testimonialRules('nullable');
    }
}
