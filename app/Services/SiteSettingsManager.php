<?php

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class SiteSettingsManager
{
    private const CACHE_KEY = 'cms.site-settings.main.v1';

    public function current(): SiteSetting
    {
        return Cache::rememberForever(self::CACHE_KEY, function (): SiteSetting {
            return SiteSetting::query()->firstOrNew(['singleton_key' => 'main'], [
                'site_name' => config('seo.site_title', config('app.name', 'Sitio web')),
                'locale' => config('app.locale', 'es'),
                'presentation_timezone' => config('app.timezone', 'America/Bogota'),
            ]);
        });
    }

    /** @param array<string, mixed> $data */
    public function update(array $data): SiteSetting
    {
        $current = $this->current();
        $oldLogo = $current->logo_path;
        $oldFavicon = $current->favicon_path;
        $newFiles = [];

        foreach (['logo', 'favicon'] as $field) {
            if (($data[$field] ?? null) instanceof UploadedFile) {
                $path = $data[$field]->store('site/branding', 'public');

                if (! is_string($path) || $path === '') {
                    $this->removeStoredFiles($newFiles);
                    throw new RuntimeException("No se pudo almacenar el archivo de identidad {$field}.");
                }

                $newFiles[$field] = $path;
            }
        }

        try {
            DB::transaction(function () use ($data, $newFiles): void {
                $settings = SiteSetting::query()->firstOrNew(['singleton_key' => 'main']);
                $settings->site_name = $data['site_name'];
                $settings->contact_email = $data['contact_email'] ?? null;
                $settings->phone = $data['phone'] ?? null;
                $settings->address = $data['address'] ?? null;
                $settings->description = $data['description'] ?? null;
                $settings->footer_text = $data['footer_text'] ?? null;
                $settings->locale = $data['locale'];
                $settings->presentation_timezone = $data['presentation_timezone'];

                foreach (['logo', 'favicon'] as $field) {
                    $pathColumn = $field.'_path';

                    if (isset($newFiles[$field])) {
                        $settings->{$pathColumn} = $newFiles[$field];
                    } elseif ((bool) ($data['remove_'.$field] ?? false)) {
                        $settings->{$pathColumn} = null;
                    }
                }

                $settings->save();
                app(SeoSettingsManager::class)->syncSiteTitle($settings->site_name);
            });
        } catch (Throwable $exception) {
            $this->removeStoredFiles(array_values($newFiles));
            throw $exception;
        }

        $this->forgetCache();
        $updated = SiteSetting::query()->where('singleton_key', 'main')->firstOrFail();

        foreach ([['logo_path', $oldLogo], ['favicon_path', $oldFavicon]] as [$column, $oldPath]) {
            if ($oldPath && $oldPath !== $updated->{$column}) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        return $this->current();
    }

    public function syncSiteName(string $siteName): void
    {
        $settings = SiteSetting::query()->firstOrNew(['singleton_key' => 'main'], [
            'locale' => config('app.locale', 'es'),
            'presentation_timezone' => config('app.timezone', 'America/Bogota'),
        ]);
        $settings->site_name = $siteName;
        $settings->save();
        $this->forgetCache();
    }

    public function forgetCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /** @param list<string> $paths */
    private function removeStoredFiles(array $paths): void
    {
        foreach ($paths as $path) {
            Storage::disk('public')->delete($path);
        }
    }
}
