<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AboutPage extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'title',
        'subtitle',
        'content',
        'primary_image',
        'mission',
        'vision',
        'values',
        'history',
        'secondary_image',
        'is_active',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    /** @param Builder<static> $query @return Builder<static> */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
