<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AuditLogIndexRequest;
use App\Models\AuditLog;
use App\Models\Banner;
use App\Models\MailSetting;
use App\Models\Post;
use App\Models\SeoSetting;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(AuditLogIndexRequest $request): View
    {
        $filters = $request->validated();
        $logs = AuditLog::query()->with('user')
            ->when($filters['user_id'] ?? null, fn ($query, $userId) => $query->where('user_id', $userId))
            ->when($filters['event'] ?? null, fn ($query, $event) => $query->where('event', $event))
            ->when($filters['model'] ?? null, fn ($query, $model) => $query->where('model_type', $model))
            ->when($filters['from'] ?? null, fn ($query, $date) => $query->whereDate('created_at', '>=', $date))
            ->when($filters['to'] ?? null, fn ($query, $date) => $query->whereDate('created_at', '<=', $date))
            ->orderByDesc('created_at')->orderByDesc('id')->paginate(25)->withQueryString();

        return view('admin.audit.index', [
            'logs' => $logs,
            'users' => User::query()->select(['id', 'name', 'email'])->orderBy('name')->get(),
            'events' => AuditLogger::EVENTS,
            'models' => [
                User::class => 'Usuario',
                Banner::class => 'Banner',
                Post::class => 'Publicación',
                SiteSetting::class => 'Configuración',
                SeoSetting::class => 'Configuración SEO',
                MailSetting::class => 'Correo SMTP',
            ],
            'filters' => $filters,
        ]);
    }
}
