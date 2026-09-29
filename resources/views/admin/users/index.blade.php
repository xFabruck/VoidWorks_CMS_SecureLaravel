@extends('layouts.admin')

@section('title', 'Usuarios')
@section('breadcrumb', 'ADMINISTRACIÓN / USUARIOS')

@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div><p class="mono-label text-cyan">// CONTROL DE ACCESO</p><h1 class="mt-2 font-display text-3xl font-semibold text-white">Usuarios</h1><p class="mt-2 text-sm text-white/55">Administra cuentas, roles y acceso al panel.</p></div>
        <a href="{{ route('admin.users.create') }}" class="focus-cyan rounded-sm bg-cyan px-4 py-3 font-mono text-xs font-semibold tracking-wider text-void">CREAR USUARIO</a>
    </div>
    @if (session('status'))<div role="status" class="rounded-sm border border-emerald-400/20 bg-emerald-400/5 p-4 text-sm text-emerald-200">{{ session('status') }}</div>@endif
    @if ($errors->has('status'))<div role="alert" class="rounded-sm border border-rose-400/20 bg-rose-400/5 p-4 text-sm text-rose-200">{{ $errors->first('status') }}</div>@endif
    <section class="glass-panel overflow-hidden rounded-md">
        <div class="overflow-x-auto"><table class="w-full min-w-[800px] text-left text-sm">
            <thead class="border-b border-white/10 bg-black/20 text-xs uppercase tracking-wider text-white/45"><tr><th class="px-5 py-4">Nombre</th><th class="px-5 py-4">Correo</th><th class="px-5 py-4">Rol</th><th class="px-5 py-4">Estado</th><th class="px-5 py-4">Fecha</th><th class="px-5 py-4">Acciones</th></tr></thead>
            <tbody class="divide-y divide-white/5">
            @forelse ($users as $user)
                <tr><td class="px-5 py-4 font-medium text-white">{{ $user->name }}</td><td class="px-5 py-4 text-white/65">{{ $user->email }}</td><td class="px-5 py-4 text-white/65">{{ ['super_admin' => 'Super Admin', 'admin' => 'Admin', 'editor' => 'Editor', 'author' => 'Autor'][$user->role] ?? $user->role }}</td><td class="px-5 py-4"><span class="rounded-sm border px-2 py-1 text-xs {{ $user->status === 'active' ? 'border-emerald-400/20 bg-emerald-400/5 text-emerald-200' : 'border-amber-400/20 bg-amber-400/5 text-amber-200' }}">{{ ['active' => 'Activo', 'suspended' => 'Suspendido', 'blocked' => 'Bloqueado'][$user->status] }}</span></td><td class="px-5 py-4 text-white/50">{{ $user->created_at?->format('d/m/Y') }}</td><td class="px-5 py-4"><div class="flex items-center gap-3"><a class="text-cyan hover:underline" href="{{ route('admin.users.edit', $user) }}">Editar</a>
                    @if ($user->id !== auth()->id())
                    <form method="POST" action="{{ route('admin.users.status', $user) }}" class="flex items-center gap-2">@csrf @method('PATCH')<label class="sr-only" for="status-{{ $user->id }}">Estado de {{ $user->name }}</label><select id="status-{{ $user->id }}" name="status" class="rounded-sm border border-white/15 bg-[#111217] px-2 py-1 text-xs text-white"><option value="active" @selected($user->status === 'active')>Activo</option><option value="suspended" @selected($user->status === 'suspended')>Suspender</option><option value="blocked" @selected($user->status === 'blocked')>Bloquear</option></select><button class="text-xs text-white/70 hover:text-cyan">Guardar</button></form>
                    @endif
                </div></td></tr>
            @empty<tr><td colspan="6" class="px-5 py-12 text-center text-white/45">No hay usuarios registrados.</td></tr>@endforelse
            </tbody>
        </table></div>
        <div class="border-t border-white/10 p-4">{{ $users->links() }}</div>
    </section>
</div>
@endsection
