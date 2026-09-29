<?php

namespace App\Services;

use App\Models\Video;
use App\Support\VideoUrl;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class VideoManager
{
    public function paginateAdmin(): LengthAwarePaginator
    {
        return Video::query()->orderBy('position')->orderBy('id')->paginate(15);
    }

    /** @return Collection<int, Video> */
    public function active(): Collection
    {
        return Video::query()->active()->orderBy('position')->orderBy('id')->get();
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Video
    {
        $attributes = $this->attributes($data);

        return DB::transaction(fn (): Video => Video::query()->create($attributes));
    }

    /** @param array<string, mixed> $data */
    public function update(Video $video, array $data): Video
    {
        $video->update($this->attributes($data));

        return $video->refresh();
    }

    public function setActive(Video $video, bool $active): Video
    {
        $video->update(['is_active' => $active]);

        return $video->refresh();
    }

    public function delete(Video $video): void
    {
        DB::transaction(fn () => $video->delete());
    }

    /** @param array<string, mixed> $data @return array<string, mixed> */
    private function attributes(array $data): array
    {
        $provider = (string) $data['provider'];
        $videoUrl = (string) $data['video_url'];
        $videoId = VideoUrl::extractId($videoUrl, $provider);

        if ($videoId === null) {
            throw new RuntimeException('La URL del proveedor no es válida.');
        }

        $thumbnail = $data['thumbnail'] ?? null;
        if ($thumbnail === null || $thumbnail === '') {
            $thumbnail = $provider === 'youtube'
                ? 'https://i.ytimg.com/vi/'.$videoId.'/hqdefault.jpg'
                : null;
        } elseif (! VideoUrl::thumbnailAllowed((string) $thumbnail, $provider)) {
            throw new RuntimeException('La miniatura no pertenece a un CDN permitido.');
        }

        return [
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'provider' => $provider,
            'video_url' => VideoUrl::canonicalUrl($provider, $videoId),
            'video_id' => $videoId,
            'thumbnail' => $thumbnail,
            'position' => (int) $data['position'],
            'is_active' => (bool) $data['is_active'],
        ];
    }
}
