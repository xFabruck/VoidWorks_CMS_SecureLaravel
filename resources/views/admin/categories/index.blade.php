@extends('layouts.admin')

@section('title', 'Categorías')
@section('heading', 'Categorías')
@section('breadcrumb', 'CONTENIDO / CATEGORÍAS')

@section('content')
    <div class="mx-auto max-w-7xl space-y-6">
        <section class="glass-panel relative flex flex-wrap items-end justify-between gap-5 overflow-hidden rounded-md p-5 sm:p-7">
            <div class="pointer-events-none absolute right-0 top-0 h-full w-1/3 bg-gradient-to-l from-cyan/[.04] to-transparent"></div>
            <div><p class="mono-label text-cyan">// ORGANIZACIÓN DE CONTENIDO</p><h2 class="mt-2 font-display text-2xl font-semibold text-white sm:text-3xl">Categorías <span class="text-cyan">/ CMS</span></h2><p class="mt-2 max-w-2xl text-sm leading-6 text-muted">Administra las categorías disponibles para clasificar contenido.</p></div>
            @can('create', App\Models\Category::class)<a href="{{ route('admin.categories.create') }}" class="focus-cyan relative rounded-sm bg-cyan px-4 py-3 font-mono text-xs font-semibold tracking-wider text-void transition hover:brightness-110">＋ NUEVA CATEGORÍA</a>@endcan
        </section>
        @if (session('status'))<div role="status" class="rounded-sm border border-emerald-400/30 bg-emerald-400/10 p-4 text-sm text-emerald-200">{{ session('status') }}</div>@endif
        <p class="mono-label px-1 text-white/45">// INVENTARIO <span class="ml-2 text-cyan">{{ $categories->total() }} CATEGORÍAS</span></p>
        <div class="overflow-hidden rounded-md border border-white/10 bg-[#121318]/90 backdrop-blur-md">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] text-left text-sm">
                    <thead class="border-b border-white/10 bg-white/[.03] mono-label text-white/55"><tr><th scope="col" class="px-5 py-4">Nombre</th><th scope="col" class="px-5 py-4">Slug</th><th scope="col" class="px-5 py-4">Descripción</th><th scope="col" class="px-5 py-4">Estado</th><th scope="col" class="px-5 py-4">Fecha</th><th scope="col" class="px-5 py-4 text-right">Acciones</th></tr></thead>
                    <tbody class="divide-y divide-white/5">
                        @forelse ($categories as $category)
                            <tr>
                                <td class="px-5 py-4 font-medium text-white">{{ $category->name }}</td>
                                <td class="px-5 py-4 font-mono text-xs text-cyan/80">{{ $category->slug }}</td>
                                <td class="max-w-xs truncate px-5 py-4 text-xs text-white/55">{{ $category->description ?: '—' }}</td>
                                <td class="px-5 py-4">@if ($category->is_active)<span class="rounded-sm border border-emerald-400/30 bg-emerald-400/5 px-2 py-1 mono-label text-emerald-200">ACTIVA</span>@else<span class="rounded-sm border border-white/10 px-2 py-1 mono-label text-white/45">INACTIVA</span>@endif</td>
                                <td class="px-5 py-4 text-xs text-white/65">{{ $category->created_at?->format('d/m/Y H:i') }}</td>
                                <td class="px-5 py-4"><div class="flex items-center justify-end gap-2">
                                    @can('update', $category)<a href="{{ route('admin.categories.edit', $category) }}" class="focus-cyan rounded-sm px-2 py-1 text-cyan hover:bg-cyan/10">Editar</a><form method="POST" action="{{ route('admin.categories.toggle', $category) }}">@csrf @method('PATCH')<button type="submit" class="focus-cyan rounded-sm px-2 py-1 text-white/65 hover:bg-white/5">{{ $category->is_active ? 'Desactivar' : 'Activar' }}</button></form>@endcan
                                    @can('delete', $category)<form method="POST" action="{{ route('admin.categories.destroy', $category) }}" data-confirm="La categoría se ocultará, pero su registro se conservará para mantener futuras relaciones. ¿Continuar?">@csrf @method('DELETE')<button type="submit" class="focus-cyan rounded-sm px-2 py-1 text-red-300 hover:bg-red-400/10">Eliminar</button></form>@endcan
                                </div></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-12 text-center text-sm text-white/50">Todavía no hay categorías. Crea una para organizar el contenido del CMS.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($categories->hasPages())<div class="border-t border-white/10 px-5 py-4">{{ $categories->links() }}</div>@endif
        </div>
    </div>
@endsection
