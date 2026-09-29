<?php

namespace App\Services;

use App\Models\TeamMember;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class TeamMemberManager
{
    public function paginateAdmin(): LengthAwarePaginator
    {
        return TeamMember::query()->orderBy('position_order')->orderBy('id')->paginate(15);
    }

    /** @return Collection<int, TeamMember> */
    public function active(): Collection
    {
        return TeamMember::query()->active()->orderBy('position_order')->orderBy('id')->get();
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): TeamMember
    {
        $photoPath = ($data['photo'] ?? null) instanceof UploadedFile ? $this->storePhoto($data['photo']) : null;

        try {
            return DB::transaction(fn (): TeamMember => TeamMember::query()->create([
                'name' => $data['name'],
                'position' => $data['position'],
                'biography' => $data['biography'] ?? null,
                'photo' => $photoPath,
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'linkedin_url' => $data['linkedin_url'] ?? null,
                'position_order' => (int) $data['position_order'],
                'is_active' => (bool) $data['is_active'],
                'show_biography' => (bool) $data['show_biography'],
                'show_photo' => (bool) $data['show_photo'],
                'show_email' => (bool) $data['show_email'],
                'show_phone' => (bool) $data['show_phone'],
                'show_linkedin_url' => (bool) $data['show_linkedin_url'],
            ]));
        } catch (Throwable $exception) {
            if ($photoPath !== null) {
                Storage::disk('local')->delete($photoPath);
            }

            throw $exception;
        }
    }

    /** @param array<string, mixed> $data */
    public function update(TeamMember $teamMember, array $data): TeamMember
    {
        $newPhoto = ($data['photo'] ?? null) instanceof UploadedFile ? $this->storePhoto($data['photo']) : null;
        $oldPhoto = $teamMember->photo;

        try {
            $teamMember = DB::transaction(function () use ($teamMember, $data, $newPhoto): TeamMember {
                $teamMember->update([
                    'name' => $data['name'],
                    'position' => $data['position'],
                    'biography' => $data['biography'] ?? null,
                    'photo' => $newPhoto ?? $teamMember->photo,
                    'email' => $data['email'] ?? null,
                    'phone' => $data['phone'] ?? null,
                    'linkedin_url' => $data['linkedin_url'] ?? null,
                    'position_order' => (int) $data['position_order'],
                    'is_active' => (bool) $data['is_active'],
                    'show_biography' => (bool) $data['show_biography'],
                    'show_photo' => (bool) $data['show_photo'],
                    'show_email' => (bool) $data['show_email'],
                    'show_phone' => (bool) $data['show_phone'],
                    'show_linkedin_url' => (bool) $data['show_linkedin_url'],
                ]);

                return $teamMember->refresh();
            });
        } catch (Throwable $exception) {
            if ($newPhoto !== null) {
                Storage::disk('local')->delete($newPhoto);
            }

            throw $exception;
        }

        if ($newPhoto !== null && $oldPhoto !== null) {
            Storage::disk('local')->delete($oldPhoto);
        }

        return $teamMember;
    }

    public function setActive(TeamMember $teamMember, bool $active): TeamMember
    {
        $teamMember->update(['is_active' => $active]);

        return $teamMember->refresh();
    }

    public function delete(TeamMember $teamMember): void
    {
        $photo = $teamMember->photo;
        DB::transaction(fn () => $teamMember->delete());

        if ($photo !== null) {
            Storage::disk('local')->delete($photo);
        }
    }

    private function storePhoto(UploadedFile $photo): string
    {
        $path = $photo->store('team', 'local');

        if (! is_string($path) || $path === '') {
            throw new RuntimeException('No se pudo almacenar la fotografía del miembro.');
        }

        return $path;
    }
}
