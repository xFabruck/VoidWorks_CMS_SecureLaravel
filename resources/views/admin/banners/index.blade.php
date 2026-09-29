@extends('layouts.admin')

@section('title', 'Banners / Hero')
@section('heading', 'Banners / Hero')
@section('breadcrumb', 'CONTENIDO / BANNERS & HERO')

@section('content')
    <div class="mx-auto max-w-7xl space-y-6">
        <div class="glass-panel relative flex flex-wrap items-end justify-between gap-5 overflow-hidden rounded-md p-5 sm:p-7">
            <div class="pointer-events-none absolute right-0 top-0 h-full w-1/3 bg-gradient-to-l from-cyan/[.04] to-transparent"></div>
            <div>
                <p class="mono-label text-cyan">// 01_CONTENIDO PÚBLICO</p>
                <h2 class="mt-2 font-display text-2xl font-semibold text-white sm:text-3xl">Banners <span class="text-cyan">/ Hero</span></h2>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-muted">Administra el contenido destacado de la portada. Solo los banners activos y dentro de su periodo aparecen en el sitio.</p>
            </div>
            @can('create', App\Models\Banner::class)
                <a href="{{ route('admin.banners.create') }}" class="focus-cyan relative rounded-sm bg-cyan px-4 py-3 font-mono text-xs font-semibold tracking-wider text-void transition hover:brightness-110">＋ NUEVO BANNER</a>
            @endcan
        </div>

        @if (session('status'))
            <div role="status" class="rounded-sm border border-emerald-400/30 bg-emerald-400/10 p-4 text-sm text-emerald-200">{{ session('status') }}</div>
        @endif

        <div class="flex flex-wrap items-center justify-between gap-3 px-1">
            <p class="mono-label text-white/45">// INVENTARIO HERO <span class="ml-2 text-cyan">{{ $banners->total() }} REGISTROS</span></p>
            <p class="text-xs text-white/40">La programación se evalúa con la zona horaria configurada.</p>
        </div>

        <div class="overflow-hidden rounded-md border border-white/10 bg-[#121318]/90 backdrop-blur-md">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[940px] text-left text-sm">
                    <thead class="border-b border-white/10 bg-white/[.03] mono-label text-white/55">
                        <tr><th scope="col" class="px-5 py-4">Imagen</th><th scope="col" class="px-5 py-4">Título</th><th scope="col" class="px-5 py-4">Estado</th><th scope="col" class="px-5 py-4">Orden</th><th scope="col" class="px-5 py-4">Inicio</th><th scope="col" class="px-5 py-4">Fin</th><th scope="col" class="px-5 py-4 text-right">Acciones</th></tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @forelse ($banners as $banner)
                            <tr>
                                <td class="px-5 py-4"><img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($banner->image) }}" alt="{{ $banner->image_alt }}" class="h-14 w-24 rounded-sm border border-white/10 object-cover"></td>
                                <td class="px-5 py-4"><p class="font-medium text-white">{{ $banner->title }}</p><p class="mt-1 max-w-sm truncate text-xs text-white/50">{{ $banner->subtitle }}</p></td>
                                <td class="px-5 py-4">
                                    @if (! $banner->is_active)
                                        <span class="rounded-sm border border-white/10 px-2 py-1 mono-label text-white/45">DESACTIVADO</span>
                                    @elseif ($banner->starts_at && $banner->starts_at->isFuture())
                                        <span class="rounded-sm border border-violet/30 px-2 py-1 mono-label text-violet">PROGRAMADO</span>
                                    @elseif ($banner->ends_at && ! $banner->ends_at->isFuture())
                                        <span class="rounded-sm border border-red-400/30 px-2 py-1 mono-label text-red-300">EXPIRADO</span>
                                    @else
                                        <span class="rounded-sm border border-emerald-400/30 px-2 py-1 mono-label text-emerald-200">ACTIVO</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 font-mono text-white/70">{{ $banner->position }}</td>
                                <td class="px-5 py-4 text-xs text-white/65">{{ $banner->starts_at?->format('d/m/Y H:i') ?? '—' }}</td>
                                <td class="px-5 py-4 text-xs text-white/65">{{ $banner->ends_at?->format('d/m/Y H:i') ?? '—' }}</td>
                                <td class="px-5 py-4"><div class="flex items-center justify-end gap-2">
                                    @can('view', $banner)
                                        <a href="{{ route('admin.banners.preview', $banner) }}" class="focus-cyan rounded-sm px-2 py-1 text-white/65 hover:bg-white/5 hover:text-cyan">Vista previa</a>
                                    @endcan
                                    @can('update', $banner)
                                        <a href="{{ route('admin.banners.edit', $banner) }}" class="focus-cyan rounded-sm px-2 py-1 text-cyan hover:bg-cyan/10">Editar</a>
                                        <form method="POST" action="{{ route('admin.banners.toggle', $banner) }}">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="focus-cyan rounded-sm px-2 py-1 text-white/65 hover:bg-white/5">{{ $banner->is_active ? 'Desactivar' : 'Activar' }}</button>
                                        </form>
                                    @endcan
                                    @can('delete', $banner)
                                        <form method="POST" action="{{ route('admin.banners.destroy', $banner) }}" data-confirm="¿Eliminar este banner y su imagen?">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="focus-cyan rounded-sm px-2 py-1 text-red-300 hover:bg-red-400/10">Eliminar</button>
                                        </form>
                                    @endcan
                                </div></td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-5 py-12 text-center text-sm text-white/50">Todavía no hay banners. Crea el primero para preparar el hero público.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($banners->hasPages())
                <div class="border-t border-white/10 px-5 py-4">{{ $banners->links() }}</div>
            @endif
        </div>
    </div>
@endsection
