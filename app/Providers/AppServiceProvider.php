<?php

namespace App\Providers;

use App\Services\SeoSettingsManager;
use App\Services\SiteSettingsManager;
use App\Services\SocialLinkManager;
use App\Services\MailConfigurationService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(MailConfigurationService::class, fn ($app): MailConfigurationService => new MailConfigurationService(
            $app['config']->get('mail', []),
        ));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(fn ($user): ?bool => $user->isSuperAdmin() ? true : null);

        foreach (config('permissions.catalog', []) as $permission) {
            Gate::define($permission, fn ($user): bool => $user->hasPermissionTo($permission));
        }

        Gate::define('cms.manage-other-modules', fn ($user): bool => $user->hasRole(['admin', 'super_admin']));

        View::composer(['layouts.public', 'layouts.admin'], function ($view): void {
            $view->with([
                'siteSettings' => app(SiteSettingsManager::class)->current(),
                'socialLinks' => app(SocialLinkManager::class)->active(),
                'seoSettings' => app(SeoSettingsManager::class)->current(),
            ]);
        });

        View::composer('public.posts.*', function ($view): void {
            $view->with('siteSettings', app(SiteSettingsManager::class)->current());
        });
    }
}
