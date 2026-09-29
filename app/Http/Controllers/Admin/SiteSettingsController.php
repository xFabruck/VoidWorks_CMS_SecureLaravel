<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSiteSettingsRequest;
use App\Models\SocialLink;
use App\Services\AuditLogger;
use App\Services\SiteSettingsManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class SiteSettingsController extends Controller
{
    public function __construct(private readonly SiteSettingsManager $settings, private readonly AuditLogger $audit) {}

    public function edit(): View
    {
        $settings = $this->settings->current();
        Gate::authorize('view', $settings);

        return view('admin.settings.edit', [
            'settings' => $settings,
            'socialLinks' => SocialLink::query()->orderBy('position')->orderBy('id')->get(),
        ]);
    }

    public function update(UpdateSiteSettingsRequest $request): RedirectResponse
    {
        $settings = $this->settings->update($request->validated());
        $this->audit->record('settings_updated', $settings, request: $request);

        return to_route('admin.settings.edit')->with('status', 'Configuración general actualizada.');
    }
}
