<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'position', 'biography', 'photo', 'email', 'phone', 'linkedin_url',
        'position_order', 'is_active', 'show_biography', 'show_photo', 'show_email',
        'show_phone', 'show_linkedin_url',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'position_order' => 'integer',
            'is_active' => 'boolean',
            'show_biography' => 'boolean',
            'show_photo' => 'boolean',
            'show_email' => 'boolean',
            'show_phone' => 'boolean',
            'show_linkedin_url' => 'boolean',
        ];
    }

    /** @param Builder<static> $query @return Builder<static> */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
