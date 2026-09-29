@extends('layouts.admin')

@section('title', 'Mensaje de contacto')
@section('breadcrumb', 'COMUNICACIÓN / MENSAJE')

@section('content')
    <div class="mx-auto max-w-4xl space-y-6">
        <a href="{{ route('admin.contact-messages.index') }}" class="focus-cyan mono-label text-white/55 hover:text-cyan">← VOLVER A MENSAJES</a>
        @if (session('status'))<div role="status" class="rounded-sm border border-emerald-400/30 bg-emerald-400/10 p-4 text-sm text-emerald-200">{{ session('status') }}</div>@endif
        <section class="glass-panel rounded-md p-5 sm:p-8">
            <p class="mono-label text-cyan">{{ $contactMessage->created_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</p>
            <h1 class="mt-3 break-words font-display text-2xl font-semibold text-white sm:text-3xl">{{ $contactMessage->subject }}</h1>
            <dl class="mt-6 grid gap-4 border-y border-white/10 py-5 text-sm sm:grid-cols-2">
                <div><dt class="mono-label text-white/40">Nombre</dt><dd class="mt-2 break-words text-white/80">{{ $contactMessage->name }}</dd></div>
                <div><dt class="mono-label text-white/40">Correo</dt><dd class="mt-2 break-words"><a href="mailto:{{ $contactMessage->email }}" class="text-cyan hover:underline">{{ $contactMessage->email }}</a></dd></div>
                @if ($contactMessage->phone)<div><dt class="mono-label text-white/40">Teléfono</dt><dd class="mt-2 text-white/80">{{ $contactMessage->phone }}</dd></div>@endif
            </dl>
            <div class="mt-6"><h2 class="mono-label text-white/40">Mensaje</h2><p class="mt-3 whitespace-pre-wrap break-words text-sm leading-7 text-white/80">{{ $contactMessage->message }}</p></div>
            <form method="POST" action="{{ route('admin.contact-messages.update', $contactMessage) }}" class="mt-8 flex flex-wrap items-end gap-3 border-t border-white/10 pt-6">
                @csrf @method('PATCH')
                <div><label for="status" class="mono-label mb-2 block text-white/65">Estado</label><select id="status" name="status" class="rounded-sm border border-white/10 bg-[#111217] px-4 py-3 text-sm text-white outline-none focus:border-cyan">@foreach (['new' => 'Nuevo', 'read' => 'Leído', 'answered' => 'Respondido', 'archived' => 'Archivado'] as $value => $label)<option value="{{ $value }}" @selected($contactMessage->status === $value)>{{ $label }}</option>@endforeach</select></div>
                <button type="submit" class="focus-cyan rounded-sm bg-cyan px-5 py-3 font-mono text-xs font-semibold tracking-wider text-void hover:brightness-110">ACTUALIZAR ESTADO</button>
                @error('status')<p role="alert" class="text-sm text-red-300">{{ $message }}</p>@enderror
            </form>
        </section>
    </div>
@endsection
