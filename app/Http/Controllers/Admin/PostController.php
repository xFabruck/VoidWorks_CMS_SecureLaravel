<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePostRequest;
use App\Http\Requests\Admin\UpdatePostRequest;
use App\Models\Category;
use App\Models\Post;
use App\Services\AuditLogger;
use App\Services\PostManager;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PostController extends Controller
{
    public function __construct(private readonly PostManager $posts, private readonly AuditLogger $audit) {}

    public function index(): View
    {
        Gate::authorize('viewAny', Post::class);

        return view('admin.posts.index', ['posts' => $this->posts->paginateAdmin()]);
    }

    public function create(): View
    {
        Gate::authorize('create', Post::class);

        return view('admin.posts.create', ['post' => new Post, 'categories' => $this->categories()]);
    }

    public function store(StorePostRequest $request): RedirectResponse
    {
        $post = $this->posts->create($request->validated(), (int) $request->user()->getKey());
        $this->audit->record('post_created', $post, request: $request);

        return to_route('admin.posts.index')->with('status', 'Publicación guardada correctamente.');
    }

    public function edit(Post $post): View
    {
        Gate::authorize('update', $post);

        return view('admin.posts.edit', ['post' => $post, 'categories' => $this->categories()]);
    }

    public function update(UpdatePostRequest $request, Post $post): RedirectResponse
    {
        $post = $this->posts->update($post, $request->validated());
        $this->audit->record('post_updated', $post, request: $request);

        return to_route('admin.posts.index')->with('status', 'Publicación actualizada correctamente.');
    }

    public function preview(Post $post): View
    {
        Gate::authorize('view', $post);

        return view('admin.posts.preview', compact('post'));
    }

    public function publish(Post $post): RedirectResponse
    {
        Gate::authorize('publish', $post);
        $post = $this->posts->publishNow($post);
        $this->audit->record('post_updated', $post);

        return to_route('admin.posts.index')->with('status', 'Publicación publicada correctamente.');
    }

    public function unpublish(Post $post): RedirectResponse
    {
        Gate::authorize('unpublish', $post);
        $post = $this->posts->unpublish($post);
        $this->audit->record('post_updated', $post);

        return to_route('admin.posts.index')->with('status', 'Publicación devuelta a borrador.');
    }

    public function archive(Post $post): RedirectResponse
    {
        Gate::authorize('archive', $post);
        $post = $this->posts->archive($post);
        $this->audit->record('post_updated', $post);

        return to_route('admin.posts.index')->with('status', 'Publicación archivada correctamente.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        Gate::authorize('delete', $post);
        $this->posts->delete($post);

        return to_route('admin.posts.index')->with('status', 'Publicación eliminada correctamente.');
    }

    /** @return Collection<int, Category> */
    private function categories(): Collection
    {
        return Category::query()->orderBy('name')->get();
    }
}
