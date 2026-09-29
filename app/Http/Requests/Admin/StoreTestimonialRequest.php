<?php

namespace App\Http\Requests\Admin;

use App\Models\Testimonial;

class StoreTestimonialRequest extends TestimonialFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Testimonial::class) ?? false;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return $this->testimonialRules('required');
    }
}
