@extends('layouts.admin')

@section('title', 'Auditoría')
@section('breadcrumb', 'SEGURIDAD / AUDITORÍA')

@section('content')
    <section class="space-y-6">
        <div>
            <p class="mono-label text-cyan">// REGISTRO INMUTABLE DESDE EL PANEL</p>
            <h1 class="mt-2 font-display text-3xl font-semibold text-white">Auditoría</h1>
            <p class="mt-2 text-sm text-muted">Consulta acciones del sistema. Esta pantalla es de solo lectura.</p>
        </div>

        @if ($errors->any())
            <div role="alert" class="rounded-sm border border-red-400/30 bg-red-400/10 p-4 text-sm text-red-200">Revisa los filtros ingresados.</div>
        @endif

        <form method="GET" action="{{ route('admin.audit.index') }}" class="glass-panel grid gap-4 rounded-md p-4 sm:grid-cols-2 xl:grid-cols-5">
            <div>
                <label for="user_id" class="mb-2 block text-xs text-white/65">Usuario</label>
                <select id="user_id" name="user_id" class="w-full rounded-sm border border-white/10 bg-[#111217] px-3 py-2.5 text-sm text-white">
                    <option value="">Todos</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" @selected(($filters['user_id'] ?? '') == $user->id)>{{ $user->name }} · {{ $user->email }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="event" class="mb-2 block text-xs text-white/65">Evento</label>
                <select id="event" name="event" class="w-full rounded-sm border border-white/10 bg-[#111217] px-3 py-2.5 text-sm text-white">
                    <option value="">Todos</option>
                    @foreach ($events as $event)
                        <option value="{{ $event }}" @selected(($filters['event'] ?? '') === $event)>{{ ucfirst(str_replace('_', ' ', $event)) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="model" class="mb-2 block text-xs text-white/65">Modelo</label>
                <select id="model" name="model" class="w-full rounded-sm border border-white/10 bg-[#111217] px-3 py-2.5 text-sm text-white">
                    <option value="">Todos</option>
                    @foreach ($models as $class => $label)
                        <option value="{{ $class }}" @selected(($filters['model'] ?? '') === $class)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="from" class="mb-2 block text-xs text-white/65">Desde</label>
                <input id="from" name="from" type="date" value="{{ $filters['from'] ?? '' }}" class="w-full rounded-sm border border-white/10 bg-[#111217] px-3 py-2.5 text-sm text-white">
            </div>
            <div>
                <label for="to" class="mb-2 block text-xs text-white/65">Hasta</label>
                <input id="to" name="to" type="date" value="{{ $filters['to'] ?? '' }}" class="w-full rounded-sm border border-white/10 bg-[#111217] px-3 py-2.5 text-sm text-white">
            </div>
            <div class="flex flex-wrap items-end gap-2 sm:col-span-2 xl:col-span-5">
                <button type="submit" class="focus-cyan rounded-sm bg-cyan px-4 py-2.5 font-mono text-xs font-semibold tracking-wider text-void">FILTRAR</button>
                <a href="{{ route('admin.audit.index') }}" class="focus-cyan rounded-sm border border-white/15 px-4 py-2.5 text-xs text-white/70 transition hover:border-cyan/50 hover:text-cyan">Limpiar filtros</a>
            </div>
        </form>

        <div class="glass-panel overflow-hidden rounded-md">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px] text-left text-sm">
                    <thead class="border-b border-white/10 bg-white/[.03] text-xs uppercase tracking-wider text-white/45">
                        <tr>
                            <th class="px-4 py-3">Fecha</th>
                            <th class="px-4 py-3">Evento</th>
                            <th class="px-4 py-3">Usuario</th>
                            <th class="px-4 py-3">Modelo</th>
                            <th class="px-4 py-3">IP</th>
                            <th class="px-4 py-3">Detalles seguros</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @forelse ($logs as $log)
                            <tr class="align-top text-white/75">
                                <td class="whitespace-nowrap px-4 py-4 text-xs">{{ $log->created_at?->timezone(config('app.timezone'))->format('Y-m-d H:i:s') }}</td>
                                <td class="px-4 py-4"><span class="rounded-sm border border-cyan/20 bg-cyan/5 px-2 py-1 text-xs text-cyan">{{ ucfirst(str_replace('_', ' ', $log->event)) }}</span></td>
                                <td class="px-4 py-4">@if ($log->user){{ $log->user->name }}<span class="mt-1 block text-xs text-white/40">{{ $log->user->email }}</span>@else<span class="text-white/40">Sistema / invitado</span>@endif</td>
                                <td class="px-4 py-4 text-xs">{{ $models[$log->model_type] ?? ($log->model_type ? class_basename($log->model_type) : '—') }}@if ($log->model_id)<span class="text-white/40"> #{{ $log->model_id }}</span>@endif</td>
                                <td class="px-4 py-4 font-mono text-xs">{{ $log->ip_address ?? '—' }}</td>
                                <td class="px-4 py-4 text-xs">
                                    @if ($log->metadata)
                                        <details>
                                            <summary class="cursor-pointer text-cyan/80">Metadata</summary>
                                            <pre class="mt-2 max-w-xs overflow-x-auto whitespace-pre-wrap break-all text-white/55">{{ json_encode($log->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                        </details>
                                    @else
                                        —
                                    @endif
                                    @if ($log->user_agent)
                                        <details class="mt-2">
                                            <summary class="cursor-pointer text-white/45">Agente</summary>
                                            <span class="mt-1 block max-w-xs break-all text-white/55">{{ $log->user_agent }}</span>
                                        </details>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-12 text-center text-sm text-white/45">No hay eventos que coincidan con los filtros.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($logs->hasPages())
                <div class="border-t border-white/10 p-4">{{ $logs->links() }}</div>
            @endif
        </div>
    </section>
@endsection
