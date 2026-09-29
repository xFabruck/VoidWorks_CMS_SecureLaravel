@extends('layouts.admin')

@section('title', 'Dashboard')
@section('breadcrumb', 'DASHBOARD CENTRAL')

@section('content')
    <div class="mx-auto max-w-7xl space-y-6">
        <section class="glass-panel relative overflow-hidden rounded-md p-6 sm:p-8 lg:p-10">
            <div class="pointer-events-none absolute inset-y-0 right-0 hidden w-1/3 bg-gradient-to-l from-violet/10 to-transparent sm:block"></div>
            <div class="relative flex flex-col justify-between gap-6 sm:flex-row sm:items-end">
                <div>
                    <p class="mono-label inline-flex rounded-sm border border-cyan/20 bg-cyan/5 px-2.5 py-1 text-cyan">CONSOLA // CMS</p>
                    <p class="mt-5 flex items-center gap-2 text-xs text-white/55"><span class="h-2 w-2 rounded-full bg-emerald-400"></span> Sesión administrativa activa</p>
                    <h1 class="mt-3 font-display text-3xl font-semibold tracking-tight text-white sm:text-4xl">Hola, <span class="text-cyan">{{ auth()->user()->name }}</span></h1>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-muted">Este resumen muestra información actual del CMS según los permisos de tu cuenta.</p>
                </div>
                <span class="mono-label rounded-sm border border-white/10 bg-black/20 px-3 py-2 text-white/45">{{ now()->timezone(config('app.timezone'))->format('d/m/Y') }}</span>
            </div>
        </section>

        <section aria-labelledby="dashboard-metrics-title">
            <div class="mb-3 flex flex-wrap items-end justify-between gap-3">
                <div><p class="mono-label text-cyan">// ESTADO ACTUAL</p><h2 id="dashboard-metrics-title" class="mt-1 font-display text-xl font-semibold text-white">Resumen</h2></div>
                <p class="text-xs text-white/40">Solo se muestran datos autorizados para tu cuenta.</p>
            </div>
            @if (count($metrics))
                <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ($metrics as $metric)
                        <a href="{{ route($metric['route']) }}" class="focus-cyan glass-panel group rounded-md p-4 transition hover:border-cyan/30 hover:bg-cyan/[.03] sm:p-5">
                            <div class="flex items-start justify-between gap-3">
                                <span class="text-sm text-white/55">{{ $metric['label'] }}</span>
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-sm border border-cyan/20 bg-cyan/5 text-lg text-cyan" aria-hidden="true">{{ $metric['icon'] }}</span>
                            </div>
                            <p class="mt-4 font-display text-3xl font-semibold tabular-nums text-white">{{ number_format($metric['value']) }}</p>
                            <span class="mt-3 inline-block text-xs text-cyan/70 transition group-hover:text-cyan">Ver sección →</span>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="glass-panel rounded-md p-5 text-sm text-white/55">Tu cuenta todavía no tiene permisos para consultar métricas del CMS.</div>
            @endif
        </section>

        <div class="grid gap-6 xl:grid-cols-2">
            @can('audit.view')
                <section aria-labelledby="dashboard-activity-title" class="glass-panel rounded-md p-5 sm:p-6">
                    <div class="flex items-center justify-between gap-3 border-b border-white/10 pb-4">
                        <div><p class="mono-label text-cyan">// TRAZABILIDAD</p><h2 id="dashboard-activity-title" class="mt-1 font-display text-lg font-semibold text-white">Actividad reciente</h2></div>
                        <a href="{{ route('admin.audit.index') }}" class="text-xs text-cyan/75 transition hover:text-cyan">Ver auditoría →</a>
                    </div>
                    <div class="mt-4 space-y-3">
                        @forelse ($activity as $entry)
                            <div class="flex items-start justify-between gap-4 border-b border-white/5 pb-3 last:border-0 last:pb-0">
                                <div class="min-w-0">
                                    <p class="text-sm text-white/80">{{ ucfirst(str_replace('_', ' ', $entry->event)) }}</p>
                                    <p class="mt-1 truncate text-xs text-white/45">{{ $entry->user?->name ?? 'Sistema / invitado' }}@if ($entry->model_type) · {{ class_basename($entry->model_type) }}@if ($entry->model_id) #{{ $entry->model_id }}@endif @endif</p>
                                </div>
                                <time class="shrink-0 text-xs text-white/40" datetime="{{ $entry->created_at?->toIso8601String() }}">{{ $entry->created_at?->timezone(config('app.timezone'))->diffForHumans() }}</time>
                            </div>
                        @empty
                            <p class="py-5 text-sm text-white/45">Todavía no hay actividad registrada.</p>
                        @endforelse
                    </div>
                </section>

                <section aria-labelledby="dashboard-logins-title" class="glass-panel rounded-md p-5 sm:p-6">
                    <div class="border-b border-white/10 pb-4"><p class="mono-label text-cyan">// ACCESOS</p><h2 id="dashboard-logins-title" class="mt-1 font-display text-lg font-semibold text-white">Últimos accesos</h2></div>
                    <div class="mt-4 space-y-3">
                        @forelse ($logins as $login)
                            <div class="flex items-center justify-between gap-4 border-b border-white/5 pb-3 last:border-0 last:pb-0">
                                <div class="min-w-0"><p class="truncate text-sm text-white/80">{{ $login->user?->name ?? 'Usuario' }}</p><p class="mt-1 truncate text-xs text-white/45">{{ $login->user?->email }}</p></div>
                                <time class="shrink-0 text-xs text-white/40" datetime="{{ $login->created_at?->toIso8601String() }}">{{ $login->created_at?->timezone(config('app.timezone'))->diffForHumans() }}</time>
                            </div>
                        @empty
                            <p class="py-5 text-sm text-white/45">Todavía no hay accesos registrados.</p>
                        @endforelse
                    </div>
                </section>
            @endcan

            @can('posts.view')
                <section aria-labelledby="dashboard-posts-title" class="glass-panel rounded-md p-5 sm:p-6">
                    <div class="flex items-center justify-between gap-3 border-b border-white/10 pb-4">
                        <div><p class="mono-label text-cyan">// CONTENIDO EDITORIAL</p><h2 id="dashboard-posts-title" class="mt-1 font-display text-lg font-semibold text-white">Publicaciones recientes</h2></div>
                        <a href="{{ route('admin.posts.index') }}" class="text-xs text-cyan/75 transition hover:text-cyan">Ver publicaciones →</a>
                    </div>
                    <div class="mt-4 space-y-3">
                        @forelse ($recentPosts as $post)
                            <div class="flex items-start justify-between gap-4 border-b border-white/5 pb-3 last:border-0 last:pb-0">
                                <div class="min-w-0"><p class="truncate text-sm text-white/80">{{ $post->title }}</p><p class="mt-1 text-xs text-white/45">{{ $post->author?->name ?? 'Sin autor' }} · {{ $post->updated_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</p></div>
                                <span class="shrink-0 rounded-sm border border-white/10 px-2 py-1 text-[.65rem] text-white/55">{{ ucfirst($post->status) }}</span>
                            </div>
                        @empty
                            <p class="py-5 text-sm text-white/45">No hay publicaciones en tu ámbito de acceso.</p>
                        @endforelse
                    </div>
                </section>
            @endcan

            @can('cms.manage-other-modules')
                <section aria-labelledby="dashboard-messages-title" class="glass-panel rounded-md p-5 sm:p-6">
                    <div class="flex items-center justify-between gap-3 border-b border-white/10 pb-4">
                        <div><p class="mono-label text-cyan">// BANDEJA DE ENTRADA</p><h2 id="dashboard-messages-title" class="mt-1 font-display text-lg font-semibold text-white">Mensajes pendientes</h2></div>
                        <a href="{{ route('admin.contact-messages.index') }}" class="text-xs text-cyan/75 transition hover:text-cyan">Ver mensajes →</a>
                    </div>
                    <div class="mt-4 space-y-3">
                        @forelse ($pendingMessages as $message)
                            <a href="{{ route('admin.contact-messages.show', $message) }}" class="focus-cyan block border-b border-white/5 pb-3 last:border-0 last:pb-0">
                                <div class="flex items-start justify-between gap-4"><p class="truncate text-sm text-white/80">{{ $message->subject }}</p><time class="shrink-0 text-xs text-white/40" datetime="{{ $message->created_at?->toIso8601String() }}">{{ $message->created_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</time></div>
                                <p class="mt-1 truncate text-xs text-white/45">{{ $message->name }}</p>
                            </a>
                        @empty
                            <p class="py-5 text-sm text-white/45">No hay mensajes nuevos.</p>
                        @endforelse
                    </div>
                </section>
            @endcan
        </div>
    </div>
@endsection
