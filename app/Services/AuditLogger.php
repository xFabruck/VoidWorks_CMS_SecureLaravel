<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditLogger
{
    private const ROLES = ['super_admin', 'admin', 'editor', 'author'];

    public const EVENTS = [
        'login', 'logout', 'login_failed',
        'password_reset_requested', 'password_changed',
        'user_created', 'user_updated', 'role_changed',
        'banner_created', 'banner_updated',
        'post_created', 'post_updated',
        'settings_updated', 'smtp_updated',
    ];

    /** @param array<string, mixed> $metadata */
    public function record(
        string $event,
        ?Model $subject = null,
        array $metadata = [],
        ?int $actorId = null,
        ?Request $request = null,
    ): void {
        if (! in_array($event, self::EVENTS, true)) {
            return;
        }

        $request ??= request();
        $log = new AuditLog;
        $log->user_id = $actorId ?? Auth::id();
        $log->event = $event;
        $log->model_type = $subject?->getMorphClass();
        $log->model_id = $subject?->getKey();
        $log->ip_address = $request->ip();
        $log->user_agent = mb_substr((string) $request->userAgent(), 0, 1000);
        $log->metadata = $this->safeMetadata($event, $metadata);
        $log->created_at = now();
        $log->save();
    }

    /** @param array<string, mixed> $metadata @return array<string, string> */
    private function safeMetadata(string $event, array $metadata): array
    {
        if ($event === 'user_created' && in_array($metadata['role'] ?? null, self::ROLES, true)) {
            return ['role' => $metadata['role']];
        }

        if ($event === 'role_changed') {
            return array_filter([
                'from' => in_array($metadata['from'] ?? null, self::ROLES, true) ? $metadata['from'] : null,
                'to' => in_array($metadata['to'] ?? null, self::ROLES, true) ? $metadata['to'] : null,
            ], static fn (?string $role): bool => $role !== null);
        }

        return [];
    }
}
