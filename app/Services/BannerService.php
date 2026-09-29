<?php

namespace App\Services;

use App\Models\Banner;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class BannerService
{
    public function all(): LengthAwarePaginator
    {
        return Banner::query()->orderBy('position')->orderBy('id')->paginate(15);
    }

    /** @return Collection<int, Banner> */
    public function visible(): Collection
    {
        return Banner::query()->currentlyVisible()->orderBy('position')->orderBy('id')->get();
    }

    /** @param array<string, mixed> $data */
    public function create(array $data, int $createdBy): Banner
    {
        $imagePath = $this->storeImage($data['image']);
        unset($data['image']);

        try {
            return DB::transaction(fn (): Banner => Banner::query()->create([
                ...$data,
                'image' => $imagePath,
                'created_by' => $createdBy,
            ]));
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($imagePath);

            throw $exception;
        }
    }

    /** @param array<string, mixed> $data */
    public function update(Banner $banner, array $data): Banner
    {
        $newImagePath = null;
        $oldImagePath = $banner->image;

        if (($data['image'] ?? null) instanceof UploadedFile) {
            $newImagePath = $this->storeImage($data['image']);
        }

        unset($data['image']);

        try {
            $banner = DB::transaction(function () use ($banner, $data, $newImagePath): Banner {
                $banner->update([
                    ...$data,
                    ...($newImagePath ? ['image' => $newImagePath] : []),
                ]);

                return $banner->refresh();
            });
        } catch (Throwable $exception) {
            if ($newImagePath !== null) {
                Storage::disk('public')->delete($newImagePath);
            }

            throw $exception;
        }

        if ($newImagePath !== null) {
            Storage::disk('public')->delete($oldImagePath);
        }

        return $banner;
    }

    public function setActive(Banner $banner, bool $active): Banner
    {
        $banner->update(['is_active' => $active]);

        return $banner->refresh();
    }

    public function delete(Banner $banner): void
    {
        $imagePath = $banner->image;

        DB::transaction(fn () => $banner->delete());

        Storage::disk('public')->delete($imagePath);
    }

    private function storeImage(UploadedFile $image): string
    {
        $path = $image->store('banners', 'public');

        if (! is_string($path) || $path === '') {
            throw new RuntimeException('No se pudo almacenar la imagen del banner.');
        }

        return $path;
    }
}
