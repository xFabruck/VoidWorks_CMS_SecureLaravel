<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class PostManager
{
    public function paginateAdmin(): LengthAwarePaginator
    {
        return Post::query()->with(['category', 'author'])->latest('updated_at')->paginate(15);
    }

    public function paginatePublic(): LengthAwarePaginator
    {
        return Post::query()->publiclyVisible()->with(['category', 'author'])
            ->orderByDesc('published_at')->orderByDesc('id')->paginate(9);
    }

    public function findPublicBySlug(string $slug): Post
    {
        return Post::query()->publiclyVisible()->with(['category', 'author'])->where('slug', $slug)->firstOrFail();
    }

    /** @param array<string, mixed> $data */
    public function create(array $data, int $authorId): Post
    {
        $image = $data['featured_image'] ?? null;
        unset($data['featured_image']);

        if ($image instanceof UploadedFile) {
            $data['featured_image'] = $this->storeImage($image);
        }

        $data['slug'] = $this->uniqueSlug($data['slug'] ?? null, $data['title']);
        $data['author_id'] = $authorId;
        $data = $this->normalizePublication($data);

        try {
            return DB::transaction(fn (): Post => Post::query()->create($data));
        } catch (Throwable $exception) {
            if (isset($data['featured_image'])) {
                Storage::disk('public')->delete($data['featured_image']);
            }

            throw $exception;
        }
    }

    /** @param array<string, mixed> $data */
    public function update(Post $post, array $data): Post
    {
        $image = $data['featured_image'] ?? null;
        unset($data['featured_image']);
        $oldImage = $post->featured_image;
        $newImage = $image instanceof UploadedFile ? $this->storeImage($image) : null;

        if ($newImage !== null) {
            $data['featured_image'] = $newImage;
        }

        $data['slug'] = $this->uniqueSlug($data['slug'] ?? $post->slug, $data['title'], $post->getKey());
        $data = $this->normalizePublication($data);

        try {
            $post = DB::transaction(function () use ($post, $data): Post {
                $post->update($data);

                return $post->refresh();
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

        return $post;
    }

    public function publishNow(Post $post): Post
    {
        $post->update(['status' => 'published', 'published_at' => now()]);

        return $post->refresh();
    }

    public function unpublish(Post $post): Post
    {
        $post->update(['status' => 'draft', 'published_at' => null]);

        return $post->refresh();
    }

    public function archive(Post $post): Post
    {
        $post->update(['status' => 'archived']);

        return $post->refresh();
    }

    public function delete(Post $post): void
    {
        $image = $post->featured_image;
        DB::transaction(fn () => $post->delete());

        if ($image !== null) {
            Storage::disk('public')->delete($image);
        }
    }

    public function publishDueScheduled(): int
    {
        return Post::query()->where('status', 'scheduled')->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->update(['status' => 'published', 'updated_at' => now()]);
    }

    /** @param array<string, mixed> $data @return array<string, mixed> */
    private function normalizePublication(array $data): array
    {
        if ($data['status'] === 'draft') {
            $data['published_at'] = null;
        } elseif ($data['status'] === 'published') {
            if (! ($data['published_at'] ?? null)) {
                $data['published_at'] = now();
            } elseif (now()->lt($data['published_at'])) {
                $data['status'] = 'scheduled';
            }
        }

        return $data;
    }

    private function storeImage(UploadedFile $image): string
    {
        $path = $image->store('posts', 'public');

        if (! is_string($path) || $path === '') {
            throw new RuntimeException('No se pudo almacenar la imagen de la publicación.');
        }

        return $path;
    }

    private function uniqueSlug(?string $requestedSlug, string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($requestedSlug ?: $title);
        $base = $base !== '' ? $base : 'publicacion';
        $slug = $base;
        $suffix = 2;

        while (Post::query()->where('slug', $slug)->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
