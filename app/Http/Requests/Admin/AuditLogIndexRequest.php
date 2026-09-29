<?php

namespace App\Http\Requests\Admin;

use App\Models\Banner;
use App\Models\MailSetting;
use App\Models\Post;
use App\Models\SeoSetting;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AuditLogIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('audit.view') ?? false;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'event' => ['nullable', Rule::in(AuditLogger::EVENTS)],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'model' => ['nullable', Rule::in([User::class, Banner::class, Post::class, SiteSetting::class, SeoSetting::class, MailSetting::class])],
        ];
    }
}
