@extends('layouts.auth')

@section('title', 'Iniciar sesión')

@section('content')
    <section class="mx-auto grid min-h-[calc(100vh-145px)] max-w-7xl items-center gap-8 px-5 py-12 lg:grid-cols-[1.15fr_.85fr] lg:px-10">
        <div class="glass-panel rounded-md p-6 sm:p-10 lg:p-12">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <p class="mono-label rounded-sm bg-white/5 px-3 py-2 text-white/65">NÚCLEO DE ACCESO // VOIDWORKS CMS</p>
                <p class="mono-label flex items-center gap-2 text-emerald-300"><span class="h-2 w-2 rounded-full bg-emerald-400"></span> CONEXIÓN SEGURA</p>
            </div>
            <p class="mono-label mt-10 text-cyan">AUTH_GATEWAY // 01_LOGIN</p>
            <h1 class="mt-3 font-display text-3xl font-semibold tracking-tight text-white sm:text-4xl">Acceso a la consola</h1>
            <p class="mt-3 text-sm leading-6 text-muted">Ingresa con las credenciales de tu cuenta para continuar.</p>

            @if (session('status'))
                <div role="status" class="mt-6 rounded-sm border border-emerald-400/30 bg-emerald-400/10 p-3 text-sm text-emerald-200">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('login.store') }}" class="mt-8 space-y-5">
                @csrf
                <div>
                    <label for="email" class="mono-label mb-2 block text-white/65">Correo electrónico</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="username" autofocus maxlength="255" aria-describedby="email-error" @class(['w-full rounded-sm border bg-black/35 px-4 py-3.5 text-sm text-white outline-none transition placeholder:text-white/30 focus:border-cyan focus:ring-2 focus:ring-cyan/15', 'border-red-400/70' => $errors->has('email'), 'border-white/10' => ! $errors->has('email')]) placeholder="nombre@estudio.com">
                    @error('email')<p id="email-error" role="alert" class="mt-2 text-sm text-red-300">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="password" class="mono-label mb-2 block text-white/65">Contraseña</label>
                    <input id="password" name="password" type="password" required autocomplete="current-password" maxlength="255" aria-describedby="password-error" @class(['w-full rounded-sm border bg-black/35 px-4 py-3.5 text-sm text-white outline-none transition placeholder:text-white/30 focus:border-cyan focus:ring-2 focus:ring-cyan/15', 'border-red-400/70' => $errors->has('password'), 'border-white/10' => ! $errors->has('password')]) placeholder="••••••••••••">
                    @error('password')<p id="password-error" role="alert" class="mt-2 text-sm text-red-300">{{ $message }}</p>@enderror
                </div>
                <label class="flex items-center gap-3 text-sm text-white/60">
                    <input type="checkbox" name="remember" value="1" class="h-4 w-4 accent-cyan">
                    Recordar esta sesión
                </label>
                <button type="submit" class="focus-cyan w-full rounded-sm bg-cyan px-5 py-4 font-mono text-xs font-semibold tracking-[.15em] text-void transition hover:brightness-110">INICIAR SESIÓN</button>
            </form>
            <a href="{{ route('password.request') }}" class="mt-5 inline-block text-sm text-white/55 transition hover:text-cyan">¿Olvidaste tu contraseña?</a>
            <p class="mt-8 border-t border-white/10 pt-5 mono-label text-white/35">LAS CREDENCIALES SE VALIDAN DE FORMA SEGURA EN EL SERVIDOR.</p>
        </div>

        <aside class="hidden space-y-5 lg:block" aria-label="Información de acceso">
            <div class="relative overflow-hidden rounded-md border border-white/10 bg-[radial-gradient(ellipse_at_65%_20%,rgba(0,242,254,.2),transparent_55%),linear-gradient(135deg,#151722,#0b0c11)] p-8">
                <img src="{{ asset('images/voidworks-emblem.svg') }}" alt="" class="mx-auto w-40 opacity-90 drop-shadow-[0_0_28px_rgba(0,242,254,.25)]">
                <div class="mt-5 flex justify-between mono-label text-white/45"><span>VOID CORE // ADMIN</span><span class="text-cyan">READY</span></div>
            </div>
            <div class="glass-panel rounded-md p-6">
                <h2 class="mono-label text-cyan">// ACCESO PROTEGIDO</h2>
                <p class="mt-4 text-sm leading-6 text-muted">El área administrativa requiere una sesión autenticada. Los intentos de acceso están limitados para proteger tu cuenta.</p>
            </div>
        </aside>
    </section>
@endsection
