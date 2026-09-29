<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBannerRequest;
use App\Http\Requests\Admin\UpdateBannerRequest;
use App\Models\Banner;
use App\Services\AuditLogger;
use App\Services\BannerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class BannerController extends Controller
{
    public function __construct(private readonly BannerService $banners, private readonly AuditLogger $audit) {}

    public function index(): View
    {
        Gate::authorize('viewAny', Banner::class);

        return view('admin.banners.index', ['banners' => $this->banners->all()]);
    }

    public function create(): View
    {
        Gate::authorize('create', Banner::class);

        return view('admin.banners.create', ['banner' => new Banner]);
    }

    public function store(StoreBannerRequest $request): RedirectResponse
    {
        Gate::authorize('create', Banner::class);
        $banner = $this->banners->create($request->validated(), (int) $request->user()->getKey());
        $this->audit->record('banner_created', $banner, request: $request);

        return to_route('admin.banners.index')->with('status', 'Banner creado correctamente.');
    }

    public function edit(Banner $banner): View
    {
        Gate::authorize('update', $banner);

        return view('admin.banners.edit', compact('banner'));
    }

    public function update(UpdateBannerRequest $request, Banner $banner): RedirectResponse
    {
        Gate::authorize('update', $banner);
        $banner = $this->banners->update($banner, $request->validated());
        $this->audit->record('banner_updated', $banner, request: $request);

        return to_route('admin.banners.index')->with('status', 'Banner actualizado correctamente.');
    }

    public function destroy(Banner $banner): RedirectResponse
    {
        Gate::authorize('delete', $banner);
        $this->banners->delete($banner);

        return to_route('admin.banners.index')->with('status', 'Banner eliminado correctamente.');
    }

    public function preview(Banner $banner): View
    {
        Gate::authorize('view', $banner);

        return view('admin.banners.preview', [
            'banners' => collect([$banner]),
            'isPreview' => true,
        ]);
    }

    public function toggle(Banner $banner): RedirectResponse
    {
        Gate::authorize('update', $banner);
        $banner = $this->banners->setActive($banner, ! $banner->is_active);
        $this->audit->record('banner_updated', $banner);

        return to_route('admin.banners.index')->with('status', 'Estado del banner actualizado.');
    }
}
