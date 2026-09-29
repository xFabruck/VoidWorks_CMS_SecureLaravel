@extends('layouts.admin')

@section('title', 'Servicios')
@section('heading', 'Servicios')
@section('breadcrumb', 'CONTENIDO / SERVICIOS')

@section('content')
    <div class="mx-auto max-w-7xl space-y-6">
        <section class="glass-panel relative flex flex-wrap items-end justify-between gap-5 overflow-hidden rounded-md p-5 sm:p-7">
            <div class="pointer-events-none absolute right-0 top-0 h-full w-1/3 bg-gradient-to-l from-cyan/[.04] to-transparent"></div>
            <div><p class="mono-label text-cyan">// CONTENIDO PÚBLICO</p><h2 class="mt-2 font-display text-2xl font-semibold text-white sm:text-3xl">Catálogo de <span class="text-cyan">Servicios</span></h2><p class="mt-2 max-w-2xl text-sm leading-6 text-muted">Gestiona los servicios que se publican en el sitio.</p></div>
            @can('create', App\Models\Service::class)<a href="{{ route('admin.services.create') }}" class="focus-cyan relative rounded-sm bg-cyan px-4 py-3 font-mono text-xs font-semibold tracking-wider text-void transition hover:brightness-110">＋ NUEVO SERVICIO</a>@endcan
        </section>
        @if (session('status'))<div role="status" class="rounded-sm border border-emerald-400/30 bg-emerald-400/10 p-4 text-sm text-emerald-200">{{ session('status') }}</div>@endif
        <div class="flex flex-wrap items-center justify-between gap-3 px-1"><p class="mono-label text-white/45">// INVENTARIO <span class="ml-2 text-cyan">{{ $services->total() }} SERVICIOS</span></p></div>
        <div class="overflow-hidden rounded-md border border-white/10 bg-[#121318]/90 backdrop-blur-md">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[820px] text-left text-sm">
                    <thead class="border-b border-white/10 bg-white/[.03] mono-label text-white/55"><tr><th scope="col" class="px-5 py-4">Imagen / Icono</th><th scope="col" class="px-5 py-4">Nombre</th><th scope="col" class="px-5 py-4">Estado</th><th scope="col" class="px-5 py-4">Orden</th><th scope="col" class="px-5 py-4">Fecha</th><th scope="col" class="px-5 py-4 text-right">Acciones</th></tr></thead>
                    <tbody class="divide-y divide-white/5">
                        @forelse ($services as $service)
                            <tr>
                                <td class="px-5 py-4">
                                    @if ($service->image)<img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($service->image) }}" alt="" class="h-12 w-16 rounded-sm border border-white/10 object-cover">
                                    @else<span class="grid h-12 w-16 place-items-center rounded-sm border border-cyan/20 bg-cyan/5 font-mono text-xs text-cyan">{{ $service->icon ?: mb_strtoupper(mb_substr($service->name, 0, 1)) }}</span>@endif
                                </td>
                                <td class="px-5 py-4"><p class="font-medium text-white">{{ $service->name }}</p><p class="mt-1 text-xs text-white/45">/{{ $service->slug }}</p></td>
                                <td class="px-5 py-4">@if ($service->is_active)<span class="rounded-sm border border-emerald-400/30 bg-emerald-400/5 px-2 py-1 mono-label text-emerald-200">ACTIVO</span>@else<span class="rounded-sm border border-white/10 px-2 py-1 mono-label text-white/45">INACTIVO</span>@endif</td>
                                <td class="px-5 py-4 font-mono text-white/70">{{ $service->position }}</td>
                                <td class="px-5 py-4 text-xs text-white/65">{{ $service->created_at?->format('d/m/Y H:i') }}</td>
                                <td class="px-5 py-4"><div class="flex items-center justify-end gap-2">
                                    @can('update', $service)<a href="{{ route('admin.services.edit', $service) }}" class="focus-cyan rounded-sm px-2 py-1 text-cyan hover:bg-cyan/10">Editar</a><form method="POST" action="{{ route('admin.services.toggle', $service) }}">@csrf @method('PATCH')<button type="submit" class="focus-cyan rounded-sm px-2 py-1 text-white/65 hover:bg-white/5">{{ $service->is_active ? 'Desactivar' : 'Activar' }}</button></form>@endcan
                                    @can('delete', $service)<form method="POST" action="{{ route('admin.services.destroy', $service) }}" data-confirm="¿Eliminar este servicio?">@csrf @method('DELETE')<button type="submit" class="focus-cyan rounded-sm px-2 py-1 text-red-300 hover:bg-red-400/10">Eliminar</button></form>@endcan
                                </div></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-12 text-center text-sm text-white/50">Todavía no hay servicios. Crea el primero para publicarlo en el catálogo.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($services->hasPages())<div class="border-t border-white/10 px-5 py-4">{{ $services->links() }}</div>@endif
        </div>
    </div>
@endsection
