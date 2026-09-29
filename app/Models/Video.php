<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    use HasFactory;

    public const PROVIDERS = ['youtube', 'vimeo'];

    protected $fillable = [
        'title', 'description', 'provider', 'video_url', 'video_id', 'thumbnail', 'position', 'is_active',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['position' => 'integer', 'is_active' => 'boolean'];
    }

    /** @param Builder<static> $query @return Builder<static> */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function getEmbedUrlAttribute(): ?string
    {
        if ($this->provider === 'youtube' && preg_match('/^[A-Za-z0-9_-]{11}$/', $this->video_id) === 1) {
            return 'https://www.youtube-nocookie.com/embed/'.$this->video_id;
        }

        if ($this->provider === 'vimeo' && preg_match('/^[0-9]+$/', $this->video_id) === 1) {
            return 'https://player.vimeo.com/video/'.$this->video_id;
        }

        return null;
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        if ($this->thumbnail) {
            return $this->thumbnail;
        }

        return $this->provider === 'youtube' && preg_match('/^[A-Za-z0-9_-]{11}$/', $this->video_id) === 1
            ? 'https://i.ytimg.com/vi/'.$this->video_id.'/hqdefault.jpg'
            : null;
    }
}
