@extends('layouts.public')

@section('title', 'Contacto')
@section('meta_description', 'Ponte en contacto con Voidworks Studio.')

@section('content')
    <section class="mx-auto grid min-h-[65vh] max-w-7xl items-start gap-10 px-5 py-14 sm:py-20 lg:grid-cols-[.8fr_1.2fr] lg:px-10">
        <div class="max-w-xl">
            <p class="mono-label text-cyan">// CONTACTO</p>
            <h1 class="mt-4 font-display text-4xl font-semibold tracking-tight text-white sm:text-5xl">Hablemos de tu proyecto.</h1>
            <p class="mt-5 text-sm leading-7 text-muted">Cuéntanos qué tienes en mente. Revisaremos tu mensaje y nos pondremos en contacto contigo.</p>
        </div>

        <div class="glass-panel rounded-md p-5 sm:p-8">
            @if (session('status'))
                <div role="status" class="mb-6 rounded-sm border border-emerald-400/30 bg-emerald-400/10 p-4 text-sm text-emerald-200">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div role="alert" class="mb-6 rounded-sm border border-red-400/30 bg-red-400/10 p-4 text-sm text-red-200">
                    <p class="font-medium">Revisa los campos marcados:</p>
                    <ul class="mt-2 list-inside list-disc space-y-1">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <form method="POST" action="{{ route('contact.store') }}" class="space-y-5">
                @csrf
                <div class="hidden" aria-hidden="true"><label for="website">Deja este campo vacío</label><input id="website" name="website" type="text" tabindex="-1" autocomplete="off"></div>
                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="name" class="mono-label mb-2 block text-white/65">Nombre *</label>
                        <input id="name" name="name" value="{{ old('name') }}" required minlength="2" maxlength="160" autocomplete="name" aria-describedby="name-error" @class(['w-full rounded-sm border bg-black/35 px-4 py-3 text-sm text-white outline-none focus:border-cyan focus:ring-2 focus:ring-cyan/15', 'border-red-400/70' => $errors->has('name'), 'border-white/10' => ! $errors->has('name')])>
                        @error('name')<p id="name-error" role="alert" class="mt-2 text-sm text-red-300">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="email" class="mono-label mb-2 block text-white/65">Correo electrónico *</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required maxlength="255" autocomplete="email" aria-describedby="email-error" @class(['w-full rounded-sm border bg-black/35 px-4 py-3 text-sm text-white outline-none focus:border-cyan focus:ring-2 focus:ring-cyan/15', 'border-red-400/70' => $errors->has('email'), 'border-white/10' => ! $errors->has('email')])>
                        @error('email')<p id="email-error" role="alert" class="mt-2 text-sm text-red-300">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="phone" class="mono-label mb-2 block text-white/65">Teléfono · opcional</label>
                        <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" maxlength="40" autocomplete="tel" aria-describedby="phone-error" @class(['w-full rounded-sm border bg-black/35 px-4 py-3 text-sm text-white outline-none focus:border-cyan focus:ring-2 focus:ring-cyan/15', 'border-red-400/70' => $errors->has('phone'), 'border-white/10' => ! $errors->has('phone')])>
                        @error('phone')<p id="phone-error" role="alert" class="mt-2 text-sm text-red-300">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="subject" class="mono-label mb-2 block text-white/65">Asunto *</label>
                        <input id="subject" name="subject" value="{{ old('subject') }}" required minlength="2" maxlength="180" aria-describedby="subject-error" @class(['w-full rounded-sm border bg-black/35 px-4 py-3 text-sm text-white outline-none focus:border-cyan focus:ring-2 focus:ring-cyan/15', 'border-red-400/70' => $errors->has('subject'), 'border-white/10' => ! $errors->has('subject')])>
                        @error('subject')<p id="subject-error" role="alert" class="mt-2 text-sm text-red-300">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div>
                    <label for="message" class="mono-label mb-2 block text-white/65">Mensaje *</label>
                    <textarea id="message" name="message" rows="7" required minlength="10" maxlength="10000" aria-describedby="message-error" @class(['w-full rounded-sm border bg-black/35 px-4 py-3 text-sm leading-6 text-white outline-none focus:border-cyan focus:ring-2 focus:ring-cyan/15', 'border-red-400/70' => $errors->has('message'), 'border-white/10' => ! $errors->has('message')])>{{ old('message') }}</textarea>
                    @error('message')<p id="message-error" role="alert" class="mt-2 text-sm text-red-300">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="flex items-start gap-3 text-sm leading-6 text-white/65">
                        <input type="checkbox" name="privacy_accepted" value="1" required @checked(old('privacy_accepted')) class="mt-1 h-4 w-4 shrink-0 accent-cyan">
                        <span>Acepto la política de privacidad y el uso de mis datos para responder a este mensaje.</span>
                    </label>
                    @error('privacy_accepted')<p role="alert" class="mt-2 text-sm text-red-300">{{ $message }}</p>@enderror
                </div>
                <button type="submit" class="focus-cyan w-full rounded-sm bg-cyan px-5 py-4 font-mono text-xs font-semibold tracking-[.15em] text-void transition hover:brightness-110 sm:w-auto">ENVIAR MENSAJE</button>
            </form>
        </div>
    </section>
@endsection
