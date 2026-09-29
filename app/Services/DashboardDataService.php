<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Banner;
use App\Models\ContactMessage;
use App\Models\Media;
use App\Models\Post;
use App\Models\Service as CmsService;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class DashboardDataService
{
    /** @return array{metrics: array<int, array{label: string, value: int, route: string, icon: string}>, activity: Collection, logins: Collection, recentPosts: Collection, pendingMessages: Collection} */
    public function forUser(User $user): array
    {
        $metrics = [];
        $activity = collect();
        $logins = collect();
        $recentPosts = collect();
        $pendingMessages = collect();

        if ($user->can('banners.view')) {
            $metrics[] = $this->metric('Banners activos', Banner::query()->currentlyVisible()->count(), 'admin.banners.index', '▧');
        }

        if ($user->can('services.view')) {
            $metrics[] = $this->metric('Servicios activos', CmsService::query()->active()->count(), 'admin.services.index', '◇');
        }

        if ($user->can('posts.view')) {
            $posts = $this->postsVisibleTo($user);
            $counts = (clone $posts)
                ->selectRaw('COUNT(*) as total_count, SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as draft_count', ['draft'])
                ->first();

            $metrics[] = $this->metric('Publicaciones', (int) ($counts?->total_count ?? 0), 'admin.posts.index', '▤');
            $metrics[] = $this->metric('Borradores', (int) ($counts?->draft_count ?? 0), 'admin.posts.index', '✎');

            $recentPosts = (clone $posts)
                ->with('author:id,name')
                ->select(['id', 'title', 'status', 'published_at', 'updated_at', 'author_id'])
                ->latest('updated_at')
                ->limit(5)
                ->get();
        }

        if ($user->can('users.view')) {
            $metrics[] = $this->metric('Usuarios', User::query()->count(), 'admin.users.index', '♙');
        }

        if ($user->can('cms.manage-other-modules')) {
            $metrics[] = $this->metric('Mensajes nuevos', ContactMessage::query()->where('status', 'new')->count(), 'admin.contact-messages.index', '✉');
            $pendingMessages = ContactMessage::query()
                ->select(['id', 'name', 'subject', 'created_at'])
                ->where('status', 'new')
                ->latest('created_at')
                ->limit(5)
                ->get();
        }

        if ($user->can('media.view')) {
            $metrics[] = $this->metric('Multimedia', Media::query()->count(), 'admin.media.index', '▧');
        }

        if ($user->can('audit.view')) {
            $activity = AuditLog::query()->with('user:id,name')
                ->latest('created_at')->latest('id')->limit(8)->get();
            $logins = AuditLog::query()->with('user:id,name,email')
                ->where('event', 'login')->whereNotNull('user_id')
                ->latest('created_at')->latest('id')->limit(5)->get();
        }

        return compact('metrics', 'activity', 'logins', 'recentPosts', 'pendingMessages');
    }

    private function metric(string $label, int $value, string $route, string $icon): array
    {
        return compact('label', 'value', 'route', 'icon');
    }

    private function postsVisibleTo(User $user): Builder
    {
        return Post::query()->when(! $user->isSuperAdmin(), fn ($query) => $query->where('author_id', $user->getKey()));
    }
}
