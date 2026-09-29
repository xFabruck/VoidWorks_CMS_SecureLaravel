@extends('layouts.admin')

@section('title', 'Redes sociales')
@section('breadcrumb', 'CONFIGURACIÓN / REDES SOCIALES')

@section('content')
    <div class="mx-auto max-w-7xl space-y-6">
        <section class="glass-panel flex flex-wrap items-end justify-between gap-5 rounded-md p-5 sm:p-7">
            <div><p class="mono-label text-cyan">// CONFIGURACIÓN DEL SITIO</p><h1 class="mt-2 font-display text-2xl font-semibold text-white sm:text-3xl">Redes sociales</h1><p class="mt-2 max-w-2xl text-sm leading-6 text-muted">Administra los enlaces visibles del sitio. La ubicación actual se configura en SOCIAL_LINKS_LOCATION: {{ config('social.location') === 'navbar' ? 'Navbar' : 'Footer' }}.</p></div>
            <a href="{{ route('admin.social-links.create') }}" class="focus-cyan rounded-sm bg-cyan px-4 py-3 font-mono text-xs font-semibold tracking-wider text-void transition hover:brightness-110">＋ AGREGAR RED SOCIAL</a>
        </section>
        @if (session('status'))<div role="status" class="rounded-sm border border-emerald-400/30 bg-emerald-400/10 p-4 text-sm text-emerald-200">{{ session('status') }}</div>@endif
        @if ($errors->any())<div role="alert" class="rounded-sm border border-red-400/30 bg-red-400/10 p-4 text-sm text-red-200">{{ $errors->first() }}</div>@endif
        <p class="mono-label px-1 text-white/45">// ENLACES <span class="ml-2 text-cyan">{{ $socialLinks->total() }} REGISTROS</span></p>
        <div class="overflow-hidden rounded-md border border-white/10 bg-[#121318]/90 backdrop-blur-md">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] text-left text-sm">
                    <thead class="border-b border-white/10 bg-white/[.03] mono-label text-white/55"><tr><th scope="col" class="px-5 py-4">Icono</th><th scope="col" class="px-5 py-4">Red</th><th scope="col" class="px-5 py-4">URL</th><th scope="col" class="px-5 py-4">Estado</th><th scope="col" class="px-5 py-4">Orden</th><th scope="col" class="px-5 py-4 text-right">Acciones</th></tr></thead>
                    <tbody class="divide-y divide-white/5">
                        @forelse ($socialLinks as $socialLink)
                            <tr>
                                <td class="px-5 py-4">@include('components.social-icon', ['icon' => $socialLink->icon])</td>
                                <td class="px-5 py-4 font-medium text-white">{{ $socialLink->network_label }}</td>
                                <td class="max-w-xs truncate px-5 py-4 text-xs text-white/55" title="{{ $socialLink->url }}">{{ $socialLink->url }}</td>
                                <td class="px-5 py-4">@if ($socialLink->is_active)<span class="rounded-sm border border-emerald-400/30 bg-emerald-400/5 px-2 py-1 mono-label text-emerald-200">ACTIVA</span>@else<span class="rounded-sm border border-white/10 px-2 py-1 mono-label text-white/45">INACTIVA</span>@endif</td>
                                <td class="px-5 py-4 font-mono text-white/70">{{ $socialLink->position }}</td>
                                <td class="px-5 py-4"><div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.social-links.edit', $socialLink) }}" class="focus-cyan rounded-sm px-2 py-1 text-cyan hover:bg-cyan/10">Editar</a>
                                    <form method="POST" action="{{ route('admin.social-links.toggle', $socialLink) }}">@csrf @method('PATCH')<button type="submit" class="focus-cyan rounded-sm px-2 py-1 text-white/65 hover:bg-white/5">{{ $socialLink->is_active ? 'Desactivar' : 'Activar' }}</button></form>
                                    <form method="POST" action="{{ route('admin.social-links.destroy', $socialLink) }}" data-confirm="¿Eliminar este enlace social?">@csrf @method('DELETE')<button type="submit" class="focus-cyan rounded-sm px-2 py-1 text-red-300 hover:bg-red-400/10">Eliminar</button></form>
                                </div></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-12 text-center text-sm text-white/50">Todavía no hay enlaces sociales. Agrega uno para mostrarlo en el sitio.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($socialLinks->hasPages())<div class="border-t border-white/10 px-5 py-4">{{ $socialLinks->links() }}</div>@endif
        </div>
    </div>
@endsection
