<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeoSetting extends Model
{
    protected $fillable = [
        'singleton_key', 'site_title', 'default_meta_description', 'default_og_image',
        'robots_index', 'robots_follow',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['robots_index' => 'boolean', 'robots_follow' => 'boolean'];
    }
}
