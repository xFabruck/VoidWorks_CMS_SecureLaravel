<?php

namespace App\Http\Requests\Admin;

use App\Models\Video;

class StoreVideoRequest extends VideoFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Video::class) ?? false;
    }
}
