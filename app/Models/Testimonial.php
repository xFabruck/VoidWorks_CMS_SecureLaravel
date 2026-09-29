<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'position_or_company', 'testimonial', 'photo', 'rating', 'position', 'is_active',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['rating' => 'integer', 'position' => 'integer', 'is_active' => 'boolean'];
    }

    /** @param Builder<static> $query @return Builder<static> */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
