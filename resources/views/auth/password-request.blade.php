@extends('layouts.auth')

@section('title', 'Recuperar contraseña')

@section('content')
    <section class="mx-auto flex min-h-[calc(100vh-145px)] max-w-xl items-center px-5 py-12">
        <div class="glass-panel w-full rounded-md p-6 sm:p-10">
            <p class="mono-label text-cyan">RECUPERACIÓN DE ACCESO</p>
            <h1 class="mt-3 font-display text-3xl font-semibold text-white">Recuperar contraseña</h1>
            <p class="mt-3 text-sm leading-6 text-muted">Escribe el correo asociado a tu cuenta. Si corresponde a una cuenta registrada, recibirás un código de verificación.</p>
            @if (session('status'))<div role="status" class="mt-6 rounded-sm border border-emerald-400/30 bg-emerald-400/10 p-3 text-sm text-emerald-200">{{ session('status') }}</div>@endif
            @if ($errors->has('email'))<div role="alert" class="mt-6 rounded-sm border border-red-400/30 bg-red-400/10 p-3 text-sm text-red-200">{{ $errors->first('email') }}</div>@endif
            <form method="POST" action="{{ route('password.request.send') }}" class="mt-8 space-y-5">
                @csrf
                <div>
                    <label for="email" class="mono-label mb-2 block text-white/65">Correo electrónico</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email" maxlength="255" class="w-full rounded-sm border border-white/10 bg-black/35 px-4 py-3.5 text-sm text-white outline-none transition focus:border-cyan focus:ring-2 focus:ring-cyan/15">
                    @error('email')<p role="alert" class="mt-2 text-sm text-red-300">{{ $message }}</p>@enderror
                </div>
                <button type="submit" class="focus-cyan w-full rounded-sm bg-cyan px-5 py-4 font-mono text-xs font-semibold tracking-[.12em] text-void transition hover:brightness-110">ENVIAR CÓDIGO</button>
            </form>
            <a href="{{ route('login') }}" class="mt-6 inline-block text-sm text-white/55 transition hover:text-cyan">← Volver al inicio de sesión</a>
        </div>
    </section>
@endsection
