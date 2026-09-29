<?php

namespace App\Services;

use App\Models\Service as CmsService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class ServiceManager
{
    public function paginateAdmin(): LengthAwarePaginator
    {
        return CmsService::query()->orderBy('position')->orderBy('id')->paginate(15);
    }

    /** @return Collection<int, CmsService> */
    public function active(): Collection
    {
        return CmsService::query()->active()->orderBy('position')->orderBy('id')->get();
    }

    public function findActiveBySlug(string $slug): CmsService
    {
        return CmsService::query()->active()->where('slug', $slug)->firstOrFail();
    }

    /** @param array<string, mixed> $data */
    public function create(array $data, int $createdBy): CmsService
    {
        $upload = $data['image'] ?? null;
        unset($data['image']);

        if ($upload instanceof UploadedFile) {
            $data['image'] = $this->storeImage($upload);
        }

        $data['slug'] = $this->uniqueSlug($data['slug'] ?? null, $data['name']);
        $data['created_by'] = $createdBy;

        try {
            return DB::transaction(fn (): CmsService => CmsService::query()->create($data));
        } catch (Throwable $exception) {
            if (isset($data['image'])) {
                Storage::disk('public')->delete($data['image']);
            }

            throw $exception;
        }
    }

    /** @param array<string, mixed> $data */
    public function update(CmsService $service, array $data): CmsService
    {
        $upload = $data['image'] ?? null;
        unset($data['image']);
        $oldImage = $service->image;
        $newImage = $upload instanceof UploadedFile ? $this->storeImage($upload) : null;

        if ($newImage !== null) {
            $data['image'] = $newImage;
        }

        $data['slug'] = $this->uniqueSlug($data['slug'] ?? $service->slug, $data['name'], $service->getKey());

        try {
            $service = DB::transaction(function () use ($service, $data): CmsService {
                $service->update($data);

                return $service->refresh();
            });
        } catch (Throwable $exception) {
            if ($newImage !== null) {
                Storage::disk('public')->delete($newImage);
            }

            throw $exception;
        }

        if ($newImage !== null && $oldImage !== null) {
            Storage::disk('public')->delete($oldImage);
        }

        return $service;
    }

    public function setActive(CmsService $service, bool $active): CmsService
    {
        $service->update(['is_active' => $active]);

        return $service->refresh();
    }

    public function delete(CmsService $service): void
    {
        $image = $service->image;
        DB::transaction(fn () => $service->delete());

        if ($image !== null) {
            Storage::disk('public')->delete($image);
        }
    }

    private function storeImage(UploadedFile $image): string
    {
        $path = $image->store('services', 'public');

        if (! is_string($path) || $path === '') {
            throw new RuntimeException('No se pudo almacenar la imagen del servicio.');
        }

        return $path;
    }

    private function uniqueSlug(?string $requestedSlug, string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($requestedSlug ?: $name);
        $base = $base !== '' ? $base : 'servicio';
        $candidate = $base;
        $suffix = 2;

        while (CmsService::query()->where('slug', $candidate)->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))->exists()) {
            $candidate = $base.'-'.$suffix++;
        }

        return $candidate;
    }
}
