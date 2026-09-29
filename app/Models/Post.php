<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Post extends Model
{
    public const STATUSES = ['draft', 'published', 'scheduled', 'archived'];

    /** @var list<string> */
    protected $fillable = [
        'title', 'slug', 'excerpt', 'content', 'featured_image', 'category_id',
        'author_id', 'status', 'published_at', 'seo_title', 'meta_description',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['published_at' => 'datetime'];
    }

    /** @param Builder<static> $query @return Builder<static> */
    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
