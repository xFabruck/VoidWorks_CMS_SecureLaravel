@extends('layouts.auth')

@section('title', 'Verificar código')

@section('content')
    <section class="mx-auto flex min-h-[calc(100vh-145px)] max-w-xl items-center px-5 py-12">
        <div class="glass-panel w-full rounded-md p-6 sm:p-10">
            <p class="mono-label text-cyan">VERIFICACIÓN DE IDENTIDAD</p>
            <h1 class="mt-3 font-display text-3xl font-semibold text-white">Introduce tu código</h1>
            <p class="mt-3 text-sm leading-6 text-muted">Ingresa el código de seis dígitos recibido por correo. Es válido durante 10 minutos.</p>
            @if (session('status'))<div role="status" class="mt-6 rounded-sm border border-emerald-400/30 bg-emerald-400/10 p-3 text-sm text-emerald-200">{{ session('status') }}</div>@endif
            <form method="POST" action="{{ route('password.otp.verify') }}" class="mt-8 space-y-5">
                @csrf
                <div>
                    <label for="code" class="mono-label mb-2 block text-white/65">Código de verificación</label>
                    <input id="code" name="code" type="text" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" minlength="6" required autocomplete="one-time-code" class="w-full rounded-sm border border-white/10 bg-black/35 px-4 py-3.5 text-center font-mono text-xl tracking-[.5em] text-white outline-none transition focus:border-cyan focus:ring-2 focus:ring-cyan/15">
                    @error('code')<p role="alert" class="mt-2 text-sm text-red-300">{{ $message }}</p>@enderror
                </div>
                <button type="submit" class="focus-cyan w-full rounded-sm bg-cyan px-5 py-4 font-mono text-xs font-semibold tracking-[.12em] text-void transition hover:brightness-110">VERIFICAR CÓDIGO</button>
            </form>
            <form method="POST" action="{{ route('password.otp.resend') }}" class="mt-4">
                @csrf
                <button type="submit" class="focus-cyan w-full rounded-sm border border-white/15 px-5 py-3 font-mono text-xs tracking-wider text-white/70 transition hover:border-cyan/50 hover:text-cyan">REENVIAR CÓDIGO</button>
            </form>
            <a href="{{ route('password.request') }}" class="mt-6 inline-block text-sm text-white/55 transition hover:text-cyan">Usar otro correo</a>
        </div>
    </section>
@endsection
