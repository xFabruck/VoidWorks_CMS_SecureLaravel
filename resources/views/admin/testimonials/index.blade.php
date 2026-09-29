@extends('layouts.admin')

@section('title', 'Testimonios')
@section('breadcrumb', 'CONTENIDO / TESTIMONIOS')

@section('content')
    <div class="mx-auto max-w-7xl space-y-6">
        <section class="glass-panel relative flex flex-wrap items-end justify-between gap-5 overflow-hidden rounded-md p-5 sm:p-7">
            <div class="pointer-events-none absolute right-0 top-0 h-full w-1/3 bg-gradient-to-l from-cyan/[.04] to-transparent"></div>
            <div class="relative"><p class="mono-label text-cyan">// VOCES DE CLIENTES</p><h1 class="mt-2 font-display text-2xl font-semibold text-white sm:text-3xl">Administrar <span class="text-cyan">testimonios</span></h1><p class="mt-2 max-w-2xl text-sm leading-6 text-muted">Solo los testimonios activos se muestran en la página pública.</p></div>
            <a href="{{ route('admin.testimonials.create') }}" class="focus-cyan relative rounded-sm bg-cyan px-4 py-3 font-mono text-xs font-semibold tracking-wider text-void transition hover:brightness-110">＋ NUEVO TESTIMONIO</a>
        </section>
        @if (session('status'))<div role="status" class="rounded-sm border border-emerald-400/30 bg-emerald-400/10 p-4 text-sm text-emerald-200">{{ session('status') }}</div>@endif
        <p class="mono-label px-1 text-white/45">// TESTIMONIOS <span class="ml-2 text-cyan">{{ $testimonials->total() }} REGISTROS</span></p>
        <div class="overflow-hidden rounded-md border border-white/10 bg-[#121318]/90 backdrop-blur-md">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px] text-left text-sm">
                    <thead class="border-b border-white/10 bg-white/[.03] mono-label text-white/55"><tr><th scope="col" class="px-5 py-4">Foto</th><th scope="col" class="px-5 py-4">Nombre / Atribución</th><th scope="col" class="px-5 py-4">Testimonio</th><th scope="col" class="px-5 py-4">Rating</th><th scope="col" class="px-5 py-4">Estado</th><th scope="col" class="px-5 py-4">Orden</th><th scope="col" class="px-5 py-4 text-right">Acciones</th></tr></thead>
                    <tbody class="divide-y divide-white/5">
                        @forelse ($testimonials as $item)
                            <tr>
                                <td class="px-5 py-4"><img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($item->photo) }}" alt="" class="h-12 w-12 rounded-sm border border-white/10 object-cover"></td>
                                <td class="px-5 py-4"><p class="font-medium text-white">{{ $item->name }}</p><p class="mt-1 text-xs text-white/45">{{ $item->position_or_company }}</p></td>
                                <td class="px-5 py-4"><p class="max-w-sm truncate text-white/70" title="{{ $item->testimonial }}">{{ $item->testimonial }}</p></td>
                                <td class="px-5 py-4">@if ($item->rating)<span class="text-amber-300" aria-label="{{ $item->rating }} de 5">{{ str_repeat('★', $item->rating) }}</span>@else<span class="text-white/35">—</span>@endif</td>
                                <td class="px-5 py-4">@if ($item->is_active)<span class="rounded-sm border border-emerald-400/30 bg-emerald-400/5 px-2 py-1 mono-label text-emerald-200">ACTIVO</span>@else<span class="rounded-sm border border-white/10 px-2 py-1 mono-label text-white/45">INACTIVO</span>@endif</td>
                                <td class="px-5 py-4 font-mono text-white/70">{{ $item->position }}</td>
                                <td class="px-5 py-4"><div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.testimonials.edit', $item) }}" class="focus-cyan rounded-sm px-2 py-1 text-cyan hover:bg-cyan/10">Editar</a>
                                    <form method="POST" action="{{ route('admin.testimonials.toggle', $item) }}">@csrf @method('PATCH')<button type="submit" class="focus-cyan rounded-sm px-2 py-1 text-white/65 hover:bg-white/5">{{ $item->is_active ? 'Desactivar' : 'Activar' }}</button></form>
                                    <form method="POST" action="{{ route('admin.testimonials.destroy', $item) }}" data-confirm="¿Eliminar este testimonio y su fotografía?">@csrf @method('DELETE')<button type="submit" class="focus-cyan rounded-sm px-2 py-1 text-red-300 hover:bg-red-400/10">Eliminar</button></form>
                                </div></td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-5 py-12 text-center text-sm text-white/50">Todavía no hay testimonios. Crea el primero para comenzar.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($testimonials->hasPages())<div class="border-t border-white/10 px-5 py-4">{{ $testimonials->links() }}</div>@endif
        </div>
    </div>
@endsection
