<?php

namespace App\Http\Requests\Admin;

class UpdateVideoRequest extends VideoFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('video')) ?? false;
    }
}
