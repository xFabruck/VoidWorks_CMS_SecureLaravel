@extends('layouts.admin')

@section('title', 'Mensajes de contacto')
@section('breadcrumb', 'COMUNICACIÓN / CONTACTO')

@section('content')
    <div class="mx-auto max-w-7xl space-y-6">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div><p class="mono-label text-cyan">// BANDEJA DE ENTRADA</p><h1 class="mt-2 font-display text-3xl font-semibold text-white">Mensajes de contacto</h1></div>
            <p class="mono-label text-white/45">{{ $messages->total() }} MENSAJES</p>
        </div>
        @if (session('status'))<div role="status" class="rounded-sm border border-emerald-400/30 bg-emerald-400/10 p-4 text-sm text-emerald-200">{{ session('status') }}</div>@endif
        <div class="overflow-hidden rounded-md border border-white/10 bg-[#121318]/90">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] text-left text-sm">
                    <thead class="border-b border-white/10 bg-white/[.03] mono-label text-white/55"><tr><th scope="col" class="px-5 py-4">Remitente</th><th scope="col" class="px-5 py-4">Asunto</th><th scope="col" class="px-5 py-4">Estado</th><th scope="col" class="px-5 py-4">Fecha</th><th scope="col" class="px-5 py-4 text-right">Acción</th></tr></thead>
                    <tbody class="divide-y divide-white/5">
                        @forelse ($messages as $item)
                            <tr>
                                <td class="px-5 py-4"><p class="font-medium text-white">{{ $item->name }}</p><p class="mt-1 text-xs text-white/45">{{ $item->email }}</p></td>
                                <td class="px-5 py-4 text-white/75">{{ $item->subject }}</td>
                                <td class="px-5 py-4"><span @class(['rounded-sm border px-2 py-1 mono-label', 'border-cyan/30 bg-cyan/5 text-cyan' => $item->status === 'new', 'border-amber-400/30 bg-amber-400/5 text-amber-200' => $item->status === 'read', 'border-emerald-400/30 bg-emerald-400/5 text-emerald-200' => $item->status === 'answered', 'border-white/10 text-white/45' => $item->status === 'archived'])>{{ ['new' => 'NUEVO', 'read' => 'LEÍDO', 'answered' => 'RESPONDIDO', 'archived' => 'ARCHIVADO'][$item->status] }}</span></td>
                                <td class="px-5 py-4 text-xs text-white/55">{{ $item->created_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</td>
                                <td class="px-5 py-4 text-right"><a href="{{ route('admin.contact-messages.show', $item) }}" class="focus-cyan rounded-sm px-2 py-1 text-cyan hover:bg-cyan/10">Ver mensaje</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-12 text-center text-sm text-white/50">Todavía no hay mensajes de contacto.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($messages->hasPages())<div class="border-t border-white/10 px-5 py-4">{{ $messages->links() }}</div>@endif
        </div>
    </div>
@endsection
