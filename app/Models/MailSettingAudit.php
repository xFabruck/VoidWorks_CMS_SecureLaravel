<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MailSettingAudit extends Model
{
    public const UPDATED = 'configuration_updated';
    public const TEST_SENT = 'test_email_sent';
    public const TEST_FAILED = 'test_email_failed';

    public $timestamps = false;

    protected $fillable = ['mail_setting_id', 'user_id', 'action', 'created_at'];

    /** @return BelongsTo<MailSetting, $this> */
    public function mailSetting(): BelongsTo
    {
        return $this->belongsTo(MailSetting::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
