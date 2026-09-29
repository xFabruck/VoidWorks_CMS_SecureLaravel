<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateAboutPageRequest;
use App\Services\AboutPageManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AboutPageController extends Controller
{
    public function edit(AboutPageManager $aboutPages): View
    {
        $aboutPage = $aboutPages->forAdmin();
        Gate::authorize('update', $aboutPage);

        return view('admin.about.edit', compact('aboutPage'));
    }

    public function update(UpdateAboutPageRequest $request, AboutPageManager $aboutPages): RedirectResponse
    {
        $aboutPages->save($request->validated());

        return to_route('admin.about.edit')->with('status', 'La información institucional se guardó correctamente.');
    }
}
