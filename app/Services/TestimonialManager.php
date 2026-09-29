<?php

namespace App\Services;

use App\Models\Testimonial;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class TestimonialManager
{
    public function paginateAdmin(): LengthAwarePaginator
    {
        return Testimonial::query()->orderBy('position')->orderBy('id')->paginate(15);
    }

    /** @return Collection<int, Testimonial> */
    public function active(): Collection
    {
        return Testimonial::query()->active()->orderBy('position')->orderBy('id')->get();
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Testimonial
    {
        $photo = $this->storePhoto($data['photo']);

        try {
            return DB::transaction(fn (): Testimonial => Testimonial::query()->create([
                'name' => $data['name'],
                'position_or_company' => $data['position_or_company'],
                'testimonial' => $data['testimonial'],
                'photo' => $photo,
                'rating' => $data['rating'] ?? null,
                'position' => (int) $data['position'],
                'is_active' => (bool) $data['is_active'],
            ]));
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($photo);

            throw $exception;
        }
    }

    /** @param array<string, mixed> $data */
    public function update(Testimonial $testimonial, array $data): Testimonial
    {
        $newPhoto = ($data['photo'] ?? null) instanceof UploadedFile ? $this->storePhoto($data['photo']) : null;
        $oldPhoto = $testimonial->photo;

        try {
            $testimonial = DB::transaction(function () use ($testimonial, $data, $newPhoto): Testimonial {
                $testimonial->update([
                    'name' => $data['name'],
                    'position_or_company' => $data['position_or_company'],
                    'testimonial' => $data['testimonial'],
                    'photo' => $newPhoto ?? $testimonial->photo,
                    'rating' => $data['rating'] ?? null,
                    'position' => (int) $data['position'],
                    'is_active' => (bool) $data['is_active'],
                ]);

                return $testimonial->refresh();
            });
        } catch (Throwable $exception) {
            if ($newPhoto !== null) {
                Storage::disk('public')->delete($newPhoto);
            }

            throw $exception;
        }

        if ($newPhoto !== null) {
            Storage::disk('public')->delete($oldPhoto);
        }

        return $testimonial;
    }

    public function setActive(Testimonial $testimonial, bool $active): Testimonial
    {
        $testimonial->update(['is_active' => $active]);

        return $testimonial->refresh();
    }

    public function delete(Testimonial $testimonial): void
    {
        $photo = $testimonial->photo;
        DB::transaction(fn () => $testimonial->delete());
        Storage::disk('public')->delete($photo);
    }

    private function storePhoto(UploadedFile $photo): string
    {
        $path = $photo->store('testimonials', 'public');

        if (! is_string($path) || $path === '') {
            throw new RuntimeException('No se pudo almacenar la fotografía del testimonio.');
        }

        return $path;
    }
}
