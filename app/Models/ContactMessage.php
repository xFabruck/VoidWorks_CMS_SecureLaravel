<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    public const UPDATED_AT = null;

    public const STATUSES = ['new', 'read', 'answered', 'archived'];

    protected $guarded = ['id', 'created_at'];
}
