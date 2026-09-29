<?php

namespace App\Http\Requests\Admin;

use App\Models\SocialLink;

class StoreSocialLinkRequest extends SocialLinkFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', SocialLink::class) ?? false;
    }
}
