<?php

namespace App\Http\Requests\Admin;

class UpdateSocialLinkRequest extends SocialLinkFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('socialLink')) ?? false;
    }
}
