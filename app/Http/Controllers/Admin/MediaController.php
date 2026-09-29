<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreMediaRequest;
use App\Models\Media;
use App\Services\MediaLibrary;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MediaController extends Controller
{
    public function __construct(private readonly MediaLibrary $library) {}

    public function index(): View
    {
        Gate::authorize('viewAny', Media::class);

        $media = Media::query()->with('uploader')->latest()->paginate(24)->withQueryString();

        return view('admin.media.index', compact('media'));
    }

    public function store(StoreMediaRequest $request): RedirectResponse
    {
        $this->library->upload($request->file('file'), $request->validated(), (int) $request->user()->getKey());

        return to_route('admin.media.index')->with('status', 'Archivo agregado a la biblioteca multimedia.');
    }

    public function file(Media $media): BinaryFileResponse
    {
        Gate::authorize('view', $media);
        abort_unless(Storage::disk($media->disk)->exists($media->path), 404);

        $isImage = $media->type === 'image' && in_array($media->mime_type, ['image/jpeg', 'image/png', 'image/webp'], true);
        $disposition = $isImage ? 'inline' : 'attachment';

        return response()->file(Storage::disk($media->disk)->path($media->path), [
            'Content-Type' => $media->mime_type,
            'Content-Disposition' => $disposition.'; filename="'.$media->file_name.'"',
            'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "default-src 'none'; sandbox",
            'Cache-Control' => 'private, no-store',
        ]);
    }

    public function destroy(Media $media): RedirectResponse
    {
        Gate::authorize('delete', $media);
        $this->library->delete($media);

        return to_route('admin.media.index')->with('status', 'Archivo eliminado de la biblioteca.');
    }
}
