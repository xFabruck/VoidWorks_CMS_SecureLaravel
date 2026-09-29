<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSeoSettingsRequest;
use App\Services\AuditLogger;
use App\Services\SeoSettingsManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class SeoSettingsController extends Controller
{
    public function __construct(private readonly SeoSettingsManager $settings, private readonly AuditLogger $audit) {}

    public function edit(): View
    {
        $settings = $this->settings->current();
        Gate::authorize('settings.view');

        return view('admin.seo.edit', compact('settings'));
    }

    public function update(UpdateSeoSettingsRequest $request): RedirectResponse
    {
        $settings = $this->settings->current();
        Gate::authorize('settings.update');
        $settings = $this->settings->update($request->validated());
        $this->audit->record('settings_updated', $settings, request: $request);

        return to_route('admin.seo.edit')->with('status', 'Configuración SEO actualizada.');
    }
}
