<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SocialLink extends Model
{
    public const NETWORKS = [
        'facebook', 'instagram', 'linkedin', 'youtube', 'tiktok', 'x', 'whatsapp', 'other',
    ];

    protected $fillable = ['network', 'url', 'icon', 'position', 'is_active'];

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

    public function getNetworkLabelAttribute(): string
    {
        return match ($this->network) {
            'facebook' => 'Facebook',
            'instagram' => 'Instagram',
            'linkedin' => 'LinkedIn',
            'youtube' => 'YouTube',
            'tiktok' => 'TikTok',
            'x' => 'X',
            'whatsapp' => 'WhatsApp',
            default => 'Otra red social',
        };
    }
}
