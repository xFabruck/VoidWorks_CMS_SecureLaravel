@extends('layouts.admin')

@section('title', 'Videos')
@section('breadcrumb', 'CONTENIDO / VIDEOS')

@section('content')
    <div class="mx-auto max-w-7xl space-y-6">
        <section class="glass-panel relative flex flex-wrap items-end justify-between gap-5 overflow-hidden rounded-md p-5 sm:p-7">
            <div class="pointer-events-none absolute right-0 top-0 h-full w-1/3 bg-gradient-to-l from-cyan/[.04] to-transparent"></div>
            <div class="relative"><p class="mono-label text-cyan">// CONTENIDO AUDIOVISUAL</p><h1 class="mt-2 font-display text-2xl font-semibold text-white sm:text-3xl">Galería de <span class="text-cyan">videos</span></h1><p class="mt-2 max-w-2xl text-sm leading-6 text-muted">Administra videos externos publicados desde YouTube y Vimeo.</p></div>
            <a href="{{ route('admin.videos.create') }}" class="focus-cyan relative rounded-sm bg-cyan px-4 py-3 font-mono text-xs font-semibold tracking-wider text-void transition hover:brightness-110">＋ AGREGAR VIDEO</a>
        </section>
        @if (session('status'))<div role="status" class="rounded-sm border border-emerald-400/30 bg-emerald-400/10 p-4 text-sm text-emerald-200">{{ session('status') }}</div>@endif
        <p class="mono-label px-1 text-white/45">// VIDEOS <span class="ml-2 text-cyan">{{ $videos->total() }} REGISTROS</span></p>
        <div class="overflow-hidden rounded-md border border-white/10 bg-[#121318]/90 backdrop-blur-md">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px] text-left text-sm">
                    <thead class="border-b border-white/10 bg-white/[.03] mono-label text-white/55"><tr><th scope="col" class="px-5 py-4">Miniatura</th><th scope="col" class="px-5 py-4">Video</th><th scope="col" class="px-5 py-4">Proveedor</th><th scope="col" class="px-5 py-4">Estado</th><th scope="col" class="px-5 py-4">Orden</th><th scope="col" class="px-5 py-4 text-right">Acciones</th></tr></thead>
                    <tbody class="divide-y divide-white/5">
                        @forelse ($videos as $video)
                            <tr>
                                <td class="px-5 py-4">@if ($video->thumbnail_url)<img src="{{ $video->thumbnail_url }}" alt="Miniatura de {{ $video->title }}" loading="lazy" referrerpolicy="no-referrer" class="aspect-video w-28 rounded-sm border border-white/10 object-cover">@else<span class="grid aspect-video w-28 place-items-center rounded-sm border border-white/10 bg-black/20 font-mono text-cyan">▶</span>@endif</td>
                                <td class="px-5 py-4"><p class="font-medium text-white">{{ $video->title }}</p><p class="mt-1 max-w-sm truncate text-xs text-white/45">{{ $video->video_url }}</p></td>
                                <td class="px-5 py-4 text-xs uppercase text-white/65">{{ $video->provider }}</td>
                                <td class="px-5 py-4">@if ($video->is_active)<span class="rounded-sm border border-emerald-400/30 bg-emerald-400/5 px-2 py-1 mono-label text-emerald-200">ACTIVO</span>@else<span class="rounded-sm border border-white/10 px-2 py-1 mono-label text-white/45">INACTIVO</span>@endif</td>
                                <td class="px-5 py-4 font-mono text-white/70">{{ $video->position }}</td>
                                <td class="px-5 py-4"><div class="flex items-center justify-end gap-2">
                                    <a href="{{ $video->video_url }}" target="_blank" rel="noopener noreferrer" class="focus-cyan rounded-sm px-2 py-1 text-white/65 hover:bg-white/5 hover:text-cyan">Abrir proveedor</a>
                                    <a href="{{ route('admin.videos.edit', $video) }}" class="focus-cyan rounded-sm px-2 py-1 text-cyan hover:bg-cyan/10">Editar</a>
                                    <form method="POST" action="{{ route('admin.videos.toggle', $video) }}">@csrf @method('PATCH')<button type="submit" class="focus-cyan rounded-sm px-2 py-1 text-white/65 hover:bg-white/5">{{ $video->is_active ? 'Desactivar' : 'Activar' }}</button></form>
                                    <form method="POST" action="{{ route('admin.videos.destroy', $video) }}" data-confirm="¿Eliminar este video?">@csrf @method('DELETE')<button type="submit" class="focus-cyan rounded-sm px-2 py-1 text-red-300 hover:bg-red-400/10">Eliminar</button></form>
                                </div></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-12 text-center text-sm text-white/50">Todavía no hay videos. Agrega un enlace de YouTube o Vimeo para comenzar.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($videos->hasPages())<div class="border-t border-white/10 px-5 py-4">{{ $videos->links() }}</div>@endif
        </div>
    </div>
@endsection
