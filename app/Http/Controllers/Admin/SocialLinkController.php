<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSocialLinkRequest;
use App\Http\Requests\Admin\UpdateSocialLinkRequest;
use App\Models\SocialLink;
use App\Services\SocialLinkManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class SocialLinkController extends Controller
{
    public function __construct(private readonly SocialLinkManager $socialLinks) {}

    public function index(): View
    {
        Gate::authorize('viewAny', SocialLink::class);

        return view('admin.social-links.index', ['socialLinks' => $this->socialLinks->paginateAdmin()]);
    }

    public function create(): View
    {
        Gate::authorize('create', SocialLink::class);

        return view('admin.social-links.create', ['socialLink' => new SocialLink]);
    }

    public function store(StoreSocialLinkRequest $request): RedirectResponse
    {
        $this->socialLinks->create($request->validated());

        return to_route('admin.social-links.index')->with('status', 'Red social agregada correctamente.');
    }

    public function edit(SocialLink $socialLink): View
    {
        Gate::authorize('update', $socialLink);

        return view('admin.social-links.edit', compact('socialLink'));
    }

    public function update(UpdateSocialLinkRequest $request, SocialLink $socialLink): RedirectResponse
    {
        $this->socialLinks->update($socialLink, $request->validated());

        return to_route('admin.social-links.index')->with('status', 'Red social actualizada correctamente.');
    }

    public function toggle(SocialLink $socialLink): RedirectResponse
    {
        Gate::authorize('update', $socialLink);
        $this->socialLinks->setActive($socialLink, ! $socialLink->is_active);

        return to_route('admin.social-links.index')->with('status', 'Estado de la red social actualizado.');
    }

    public function destroy(SocialLink $socialLink): RedirectResponse
    {
        Gate::authorize('delete', $socialLink);
        $this->socialLinks->delete($socialLink);

        return to_route('admin.social-links.index')->with('status', 'Red social eliminada correctamente.');
    }
}
