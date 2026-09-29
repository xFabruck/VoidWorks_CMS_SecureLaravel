<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreVideoRequest;
use App\Http\Requests\Admin\UpdateVideoRequest;
use App\Models\Video;
use App\Services\VideoManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class VideoController extends Controller
{
    public function __construct(private readonly VideoManager $videos) {}

    public function index(): View
    {
        Gate::authorize('viewAny', Video::class);

        return view('admin.videos.index', ['videos' => $this->videos->paginateAdmin()]);
    }

    public function create(): View
    {
        Gate::authorize('create', Video::class);

        return view('admin.videos.create', ['video' => new Video]);
    }

    public function store(StoreVideoRequest $request): RedirectResponse
    {
        $this->videos->create($request->validated());

        return to_route('admin.videos.index')->with('status', 'Video agregado correctamente.');
    }

    public function edit(Video $video): View
    {
        Gate::authorize('update', $video);

        return view('admin.videos.edit', compact('video'));
    }

    public function update(UpdateVideoRequest $request, Video $video): RedirectResponse
    {
        $this->videos->update($video, $request->validated());

        return to_route('admin.videos.index')->with('status', 'Video actualizado correctamente.');
    }

    public function toggle(Video $video): RedirectResponse
    {
        Gate::authorize('update', $video);
        $this->videos->setActive($video, ! $video->is_active);

        return to_route('admin.videos.index')->with('status', 'Estado del video actualizado.');
    }

    public function destroy(Video $video): RedirectResponse
    {
        Gate::authorize('delete', $video);
        $this->videos->delete($video);

        return to_route('admin.videos.index')->with('status', 'Video eliminado correctamente.');
    }
}
