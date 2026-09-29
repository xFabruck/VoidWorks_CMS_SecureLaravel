<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = [
        'singleton_key', 'site_name', 'logo_path', 'favicon_path', 'contact_email',
        'phone', 'address', 'description', 'footer_text', 'locale', 'presentation_timezone',
    ];
}
