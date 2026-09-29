<?php

namespace App\Http\Requests\Admin;

use App\Models\Banner;

class UpdateBannerRequest extends BannerFormRequest
{
    public function authorize(): bool
    {
        $banner = $this->route('banner');

        return $banner instanceof Banner && ($this->user()?->can('update', $banner) ?? false);
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return $this->bannerRules('nullable');
    }
}
