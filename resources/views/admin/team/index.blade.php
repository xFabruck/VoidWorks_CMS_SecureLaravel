@extends('layouts.admin')

@section('title', 'Equipo')
@section('breadcrumb', 'CONTENIDO / EQUIPO')

@section('content')
    <div class="mx-auto max-w-7xl space-y-6">
        <section class="glass-panel relative flex flex-wrap items-end justify-between gap-5 overflow-hidden rounded-md p-5 sm:p-7">
            <div class="pointer-events-none absolute right-0 top-0 h-full w-1/3 bg-gradient-to-l from-cyan/[.04] to-transparent"></div>
            <div class="relative"><p class="mono-label text-cyan">// PERSONAS DEL ESTUDIO</p><h1 class="mt-2 font-display text-2xl font-semibold text-white sm:text-3xl">Administrar <span class="text-cyan">equipo</span></h1><p class="mt-2 max-w-2xl text-sm leading-6 text-muted">Elige qué datos de cada persona aparecen en el sitio público.</p></div>
            <a href="{{ route('admin.team.create') }}" class="focus-cyan relative rounded-sm bg-cyan px-4 py-3 font-mono text-xs font-semibold tracking-wider text-void transition hover:brightness-110">＋ AGREGAR MIEMBRO</a>
        </section>
        @if (session('status'))<div role="status" class="rounded-sm border border-emerald-400/30 bg-emerald-400/10 p-4 text-sm text-emerald-200">{{ session('status') }}</div>@endif
        <p class="mono-label px-1 text-white/45">// EQUIPO <span class="ml-2 text-cyan">{{ $teamMembers->total() }} MIEMBROS</span></p>
        <div class="overflow-hidden rounded-md border border-white/10 bg-[#121318]/90 backdrop-blur-md">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px] text-left text-sm">
                    <thead class="border-b border-white/10 bg-white/[.03] mono-label text-white/55"><tr><th scope="col" class="px-5 py-4">Foto</th><th scope="col" class="px-5 py-4">Nombre / Cargo</th><th scope="col" class="px-5 py-4">Visibilidad</th><th scope="col" class="px-5 py-4">Estado</th><th scope="col" class="px-5 py-4">Orden</th><th scope="col" class="px-5 py-4 text-right">Acciones</th></tr></thead>
                    <tbody class="divide-y divide-white/5">
                        @forelse ($teamMembers as $teamMember)
                            <tr>
                                <td class="px-5 py-4">@if ($teamMember->photo)<img src="{{ route('admin.team.photo', $teamMember) }}" alt="" class="h-12 w-12 rounded-sm border border-white/10 object-cover">@else<span class="grid h-12 w-12 place-items-center rounded-sm border border-white/10 bg-black/20 font-display text-cyan">{{ mb_strtoupper(mb_substr($teamMember->name, 0, 1)) }}</span>@endif</td>
                                <td class="px-5 py-4"><p class="font-medium text-white">{{ $teamMember->name }}</p><p class="mt-1 text-xs text-white/45">{{ $teamMember->position }}</p></td>
                                <td class="px-5 py-4"><div class="flex max-w-sm flex-wrap gap-1.5">
                                    @foreach (['show_photo' => 'FOTO', 'show_biography' => 'BIO', 'show_email' => 'CORREO', 'show_phone' => 'TELÉFONO', 'show_linkedin_url' => 'LINKEDIN'] as $field => $label)
                                        <span class="rounded-sm border px-2 py-1 mono-label {{ $teamMember->{$field} ? 'border-cyan/25 bg-cyan/5 text-cyan' : 'border-white/5 text-white/25' }}">{{ $label }}</span>
                                    @endforeach
                                </div></td>
                                <td class="px-5 py-4">@if ($teamMember->is_active)<span class="rounded-sm border border-emerald-400/30 bg-emerald-400/5 px-2 py-1 mono-label text-emerald-200">ACTIVO</span>@else<span class="rounded-sm border border-white/10 px-2 py-1 mono-label text-white/45">INACTIVO</span>@endif</td>
                                <td class="px-5 py-4 font-mono text-white/70">{{ $teamMember->position_order }}</td>
                                <td class="px-5 py-4"><div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.team.edit', $teamMember) }}" class="focus-cyan rounded-sm px-2 py-1 text-cyan hover:bg-cyan/10">Editar</a>
                                    <form method="POST" action="{{ route('admin.team.toggle', $teamMember) }}">@csrf @method('PATCH')<button type="submit" class="focus-cyan rounded-sm px-2 py-1 text-white/65 hover:bg-white/5">{{ $teamMember->is_active ? 'Desactivar' : 'Activar' }}</button></form>
                                    <form method="POST" action="{{ route('admin.team.destroy', $teamMember) }}" data-confirm="¿Eliminar a este miembro del equipo?">@csrf @method('DELETE')<button type="submit" class="focus-cyan rounded-sm px-2 py-1 text-red-300 hover:bg-red-400/10">Eliminar</button></form>
                                </div></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-12 text-center text-sm text-white/50">Todavía no hay miembros. Agrega el primer perfil para comenzar.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($teamMembers->hasPages())<div class="border-t border-white/10 px-5 py-4">{{ $teamMembers->links() }}</div>@endif
        </div>
    </div>
@endsection
