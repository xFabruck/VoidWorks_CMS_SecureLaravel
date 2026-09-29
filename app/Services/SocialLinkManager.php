<?php

namespace App\Services;

use App\Models\SocialLink;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class SocialLinkManager
{
    public function paginateAdmin(): LengthAwarePaginator
    {
        return SocialLink::query()->orderBy('position')->orderBy('id')->paginate(15);
    }

    /** @return Collection<int, SocialLink> */
    public function active(): Collection
    {
        return SocialLink::query()->active()->orderBy('position')->orderBy('id')->get();
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): SocialLink
    {
        return DB::transaction(fn (): SocialLink => SocialLink::query()->create($this->attributes($data)));
    }

    /** @param array<string, mixed> $data */
    public function update(SocialLink $socialLink, array $data): SocialLink
    {
        $socialLink->update($this->attributes($data));

        return $socialLink->refresh();
    }

    public function setActive(SocialLink $socialLink, bool $active): SocialLink
    {
        $socialLink->update(['is_active' => $active]);

        return $socialLink->refresh();
    }

    public function delete(SocialLink $socialLink): void
    {
        DB::transaction(fn () => $socialLink->delete());
    }

    /** @param array<string, mixed> $data @return array<string, mixed> */
    private function attributes(array $data): array
    {
        $network = (string) $data['network'];

        return [
            'network' => $network,
            'url' => trim((string) $data['url']),
            'icon' => $network === 'other' ? 'globe' : $network,
            'position' => (int) $data['position'],
            'is_active' => (bool) $data['is_active'],
        ];
    }
}
