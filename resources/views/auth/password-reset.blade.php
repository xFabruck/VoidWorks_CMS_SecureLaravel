@extends('layouts.auth')

@section('title', 'Nueva contraseña')

@section('content')
    <section class="mx-auto flex min-h-[calc(100vh-145px)] max-w-xl items-center px-5 py-12">
        <div class="glass-panel w-full rounded-md p-6 sm:p-10">
            <p class="mono-label text-cyan">ACTUALIZACIÓN DE ACCESO</p>
            <h1 class="mt-3 font-display text-3xl font-semibold text-white">Crea una nueva contraseña</h1>
            <p class="mt-3 text-sm leading-6 text-muted">Usa al menos 12 caracteres, incluyendo letras y números.</p>
            <form method="POST" action="{{ route('password.reset.update') }}" class="mt-8 space-y-5">
                @csrf
                <div>
                    <label for="password" class="mono-label mb-2 block text-white/65">Nueva contraseña</label>
                    <input id="password" name="password" type="password" required minlength="12" autocomplete="new-password" class="w-full rounded-sm border border-white/10 bg-black/35 px-4 py-3.5 text-sm text-white outline-none transition focus:border-cyan focus:ring-2 focus:ring-cyan/15">
                    @error('password')<p role="alert" class="mt-2 text-sm text-red-300">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="password_confirmation" class="mono-label mb-2 block text-white/65">Confirmar contraseña</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required minlength="12" autocomplete="new-password" class="w-full rounded-sm border border-white/10 bg-black/35 px-4 py-3.5 text-sm text-white outline-none transition focus:border-cyan focus:ring-2 focus:ring-cyan/15">
                </div>
                <button type="submit" class="focus-cyan w-full rounded-sm bg-cyan px-5 py-4 font-mono text-xs font-semibold tracking-[.12em] text-void transition hover:brightness-110">ACTUALIZAR CONTRASEÑA</button>
            </form>
        </div>
    </section>
@endsection
