@extends('layouts.admin')

@section('title', 'Publicaciones / Noticias')
@section('heading', 'Publicaciones / Noticias')
@section('breadcrumb', 'CONTENIDO / PUBLICACIONES')

@section('content')
    <div class="mx-auto max-w-7xl space-y-6">
        <section class="glass-panel relative flex flex-wrap items-end justify-between gap-5 overflow-hidden rounded-md p-5 sm:p-7">
            <div class="pointer-events-none absolute right-0 top-0 h-full w-1/3 bg-gradient-to-l from-cyan/[.04] to-transparent"></div>
            <div><p class="mono-label text-cyan">// CONTENIDO EDITORIAL</p><h2 class="mt-2 font-display text-2xl font-semibold text-white sm:text-3xl">Publicaciones <span class="text-cyan">/ Noticias</span></h2><p class="mt-2 max-w-2xl text-sm leading-6 text-muted">Crea borradores, programa publicaciones y administra las noticias.</p></div>
            @can('create', App\Models\Post::class)<a href="{{ route('admin.posts.create') }}" class="focus-cyan relative rounded-sm bg-cyan px-4 py-3 font-mono text-xs font-semibold tracking-wider text-void transition hover:brightness-110">＋ NUEVA PUBLICACIÓN</a>@endcan
        </section>
        @if (session('status'))<div role="status" class="rounded-sm border border-emerald-400/30 bg-emerald-400/10 p-4 text-sm text-emerald-200">{{ session('status') }}</div>@endif
        <p class="mono-label px-1 text-white/45">// EDITORIAL <span class="ml-2 text-cyan">{{ $posts->total() }} REGISTROS</span></p>
        <div class="overflow-hidden rounded-md border border-white/10 bg-[#121318]/90 backdrop-blur-md">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1050px] text-left text-sm">
                    <thead class="border-b border-white/10 bg-white/[.03] mono-label text-white/55"><tr><th scope="col" class="px-5 py-4">Imagen</th><th scope="col" class="px-5 py-4">Título / Slug</th><th scope="col" class="px-5 py-4">Categoría</th><th scope="col" class="px-5 py-4">Estado</th><th scope="col" class="px-5 py-4">Autor</th><th scope="col" class="px-5 py-4">Publicación</th><th scope="col" class="px-5 py-4 text-right">Acciones</th></tr></thead>
                    <tbody class="divide-y divide-white/5">
                        @forelse ($posts as $post)
                            <tr>
                                <td class="px-5 py-4">@if ($post->featured_image)<img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($post->featured_image) }}" alt="" class="h-12 w-16 rounded-sm border border-white/10 object-cover">@else<span class="grid h-12 w-16 place-items-center rounded-sm border border-white/10 bg-black/20 mono-label text-white/30">SIN<br>IMG</span>@endif</td>
                                <td class="px-5 py-4"><p class="font-medium text-white">{{ $post->title }}</p><p class="mt-1 max-w-xs truncate font-mono text-xs text-white/45">/{{ $post->slug }}</p></td>
                                <td class="px-5 py-4 text-xs text-white/65">{{ $post->category?->name ?? 'Categoría eliminada' }}</td>
                                <td class="px-5 py-4">
                                    @switch($post->status)
                                        @case('published')<span class="rounded-sm border border-emerald-400/30 bg-emerald-400/5 px-2 py-1 mono-label text-emerald-200">PUBLICADA</span>@break
                                        @case('scheduled')<span class="rounded-sm border border-violet/30 bg-violet/5 px-2 py-1 mono-label text-violet">PROGRAMADA</span>@break
                                        @case('archived')<span class="rounded-sm border border-white/10 px-2 py-1 mono-label text-white/45">ARCHIVADA</span>@break
                                        @default<span class="rounded-sm border border-amber-300/25 bg-amber-300/5 px-2 py-1 mono-label text-amber-200">BORRADOR</span>
                                    @endswitch
                                </td>
                                <td class="px-5 py-4 text-xs text-white/65">{{ $post->author?->name ?? 'Autor no disponible' }}</td>
                                <td class="px-5 py-4 text-xs text-white/65">{{ $post->published_at?->format('d/m/Y H:i') ?? '—' }}</td>
                                <td class="px-5 py-4"><div class="flex flex-wrap items-center justify-end gap-2">
                                    @can('view', $post)<a href="{{ route('admin.posts.preview', $post) }}" class="focus-cyan rounded-sm px-2 py-1 text-white/65 hover:bg-white/5 hover:text-cyan">Vista previa</a>@endcan
                                    @can('update', $post)<a href="{{ route('admin.posts.edit', $post) }}" class="focus-cyan rounded-sm px-2 py-1 text-cyan hover:bg-cyan/10">Editar</a>@endcan
                                    @if ($post->status === 'published')
                                        @can('unpublish', $post)<form method="POST" action="{{ route('admin.posts.unpublish', $post) }}">@csrf @method('PATCH')<button type="submit" class="focus-cyan rounded-sm px-2 py-1 text-white/65 hover:bg-white/5">Despublicar</button></form>@endcan
                                    @else
                                        @can('publish', $post)<form method="POST" action="{{ route('admin.posts.publish', $post) }}">@csrf @method('PATCH')<button type="submit" class="focus-cyan rounded-sm px-2 py-1 text-emerald-200 hover:bg-emerald-400/10">Publicar ahora</button></form>@endcan
                                    @endif
                                    @if ($post->status !== 'archived')@can('archive', $post)<form method="POST" action="{{ route('admin.posts.archive', $post) }}">@csrf @method('PATCH')<button type="submit" class="focus-cyan rounded-sm px-2 py-1 text-white/65 hover:bg-white/5">Archivar</button></form>@endcan @endif
                                    @can('delete', $post)<form method="POST" action="{{ route('admin.posts.destroy', $post) }}" data-confirm="¿Eliminar esta publicación y su imagen destacada?">@csrf @method('DELETE')<button type="submit" class="focus-cyan rounded-sm px-2 py-1 text-red-300 hover:bg-red-400/10">Eliminar</button></form>@endcan
                                </div></td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-5 py-12 text-center text-sm text-white/50">Todavía no hay publicaciones. Crea una nueva para comenzar.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($posts->hasPages())<div class="border-t border-white/10 px-5 py-4">{{ $posts->links() }}</div>@endif
        </div>
    </div>
@endsection
