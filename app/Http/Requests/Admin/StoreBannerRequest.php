<?php

namespace App\Http\Requests\Admin;

use App\Models\Banner;

class StoreBannerRequest extends BannerFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Banner::class) ?? false;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return $this->bannerRules('required');
    }
}
