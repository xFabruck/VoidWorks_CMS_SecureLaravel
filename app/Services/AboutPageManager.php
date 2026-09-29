<?php

namespace App\Services;

use App\Models\AboutPage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class AboutPageManager
{
    public function forAdmin(): AboutPage
    {
        $aboutPage = AboutPage::query()->where('singleton_key', 'main')->first();

        if ($aboutPage !== null) {
            return $aboutPage;
        }

        $aboutPage = new AboutPage;
        $aboutPage->setAttribute('singleton_key', 'main');

        return $aboutPage;
    }

    public function active(): AboutPage
    {
        return AboutPage::query()->active()->where('singleton_key', 'main')->firstOrFail();
    }

    /** @param array<string, mixed> $data */
    public function save(array $data): AboutPage
    {
        $old = $this->forAdmin();
        $stored = [];
        $oldPaths = [];

        try {
            foreach (['primary_image', 'secondary_image'] as $field) {
                $upload = $data[$field] ?? null;
                unset($data[$field]);

                if ($upload instanceof UploadedFile) {
                    $stored[$field] = $this->storeImage($upload);
                    $oldPaths[] = $old->getAttribute($field);
                    $data[$field] = $stored[$field];
                }
            }

            $saved = DB::transaction(function () use ($old, $data): AboutPage {
                $aboutPage = $old->exists ? $old : new AboutPage;
                $aboutPage->setAttribute('singleton_key', 'main');
                $aboutPage->fill($data);
                $aboutPage->save();

                return $aboutPage->refresh();
            });
        } catch (Throwable $exception) {
            foreach ($stored as $path) {
                Storage::disk('public')->delete($path);
            }

            throw $exception;
        }

        foreach ($oldPaths as $path) {
            if (is_string($path) && $path !== '') {
                Storage::disk('public')->delete($path);
            }
        }

        return $saved;
    }

    private function storeImage(UploadedFile $image): string
    {
        $path = $image->store('about', 'public');

        if (! is_string($path) || $path === '') {
            throw new RuntimeException('No se pudo almacenar la imagen institucional.');
        }

        return $path;
    }
}
