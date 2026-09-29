<?php

namespace App\Services;

use App\Models\SeoSetting;
use Illuminate\Support\Facades\DB;

class SeoSettingsManager
{
    public function current(): SeoSetting
    {
        $settings = SeoSetting::query()->where('singleton_key', 'main')->first();

        if ($settings !== null) {
            return $settings;
        }

        return new SeoSetting([
            'singleton_key' => 'main',
            'site_title' => config('seo.site_title'),
            'default_meta_description' => config('seo.default_meta_description'),
            'default_og_image' => config('seo.default_og_image'),
            'robots_index' => config('seo.robots_index'),
            'robots_follow' => config('seo.robots_follow'),
        ]);
    }

    /** @param array<string, mixed> $data */
    public function update(array $data): SeoSetting
    {
        return DB::transaction(function () use ($data): SeoSetting {
            $settings = SeoSetting::query()->firstOrNew(['singleton_key' => 'main']);
            $settings->site_title = $data['site_title'];
            $settings->default_meta_description = $data['default_meta_description'];
            $settings->default_og_image = $data['default_og_image'] ?? null;
            $settings->robots_index = (bool) $data['robots_index'];
            $settings->robots_follow = (bool) $data['robots_follow'];
            $settings->save();
            app(SiteSettingsManager::class)->syncSiteName($settings->site_title);

            return $settings->refresh();
        });
    }

    public function syncSiteTitle(string $siteTitle): void
    {
        $settings = SeoSetting::query()->firstOrNew(['singleton_key' => 'main'], [
            'default_meta_description' => config('seo.default_meta_description', 'Descripción general del sitio.'),
            'default_og_image' => config('seo.default_og_image'),
            'robots_index' => config('seo.robots_index', true),
            'robots_follow' => config('seo.robots_follow', true),
        ]);
        $settings->site_title = $siteTitle;
        $settings->save();
        app(SiteSettingsManager::class)->forgetCache();
    }
}
