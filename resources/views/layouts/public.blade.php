<!doctype html>
<html lang="{{ $siteSettings->locale }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#08090e">
    @if ($siteSettings->favicon_path)<link rel="icon" href="{{ Storage::disk('public')->url($siteSettings->favicon_path) }}">@endif
    @include('public.partials.seo-meta')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="site-shell">
    <header class="border-b border-white/10 bg-black/40 backdrop-blur-xl">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-6 px-5 py-4 lg:px-10">
            <a href="{{ route('home') }}" class="focus-cyan flex items-center gap-3 rounded-sm" aria-label="{{ $siteSettings->site_name }}, inicio">
                @if ($siteSettings->logo_path)
                    <img src="{{ Storage::disk('public')->url($siteSettings->logo_path) }}" alt="{{ $siteSettings->site_name }}" class="h-10 max-w-48 object-contain">
                @else
                    <img src="{{ asset('images/voidworks-emblem.svg') }}" alt="" class="h-9 w-9">
                    <span class="font-display text-sm font-semibold tracking-[.12em]">{{ $siteSettings->site_name }}</span>
                @endif
            </a>
            <nav aria-label="Navegación principal" class="flex items-center gap-3 sm:gap-6">
                <a href="{{ route('services.index') }}" class="focus-cyan mono-label text-white/65 transition hover:text-cyan">SERVICIOS</a>
                <a href="{{ route('about') }}" class="focus-cyan mono-label text-white/65 transition hover:text-cyan">NOSOTROS</a>
                <a href="{{ route('news.index') }}" class="focus-cyan mono-label text-white/65 transition hover:text-cyan">NOTICIAS</a>
                <a href="{{ route('videos.index') }}" class="focus-cyan mono-label text-white/65 transition hover:text-cyan">VIDEOS</a>
                <a href="{{ route('team.index') }}" class="focus-cyan mono-label text-white/65 transition hover:text-cyan">EQUIPO</a>
                <a href="{{ route('testimonials.index') }}" class="focus-cyan mono-label text-white/65 transition hover:text-cyan">TESTIMONIOS</a>
                <a href="{{ route('contact.create') }}" class="focus-cyan mono-label text-white/65 transition hover:text-cyan">CONTACTO</a>
                <span class="mono-label hidden text-white/45 sm:inline">Independent game studio</span>
                <a href="{{ route('login') }}" class="focus-cyan rounded-sm border border-cyan/40 px-4 py-2 font-mono text-xs font-semibold tracking-wider text-cyan-soft transition hover:bg-cyan hover:text-void">ACCESO CMS</a>
                @if (config('social.location') === 'navbar' && $socialLinks->isNotEmpty())
                    <span class="hidden h-6 border-l border-white/15 xl:block" aria-hidden="true"></span>
                    @foreach ($socialLinks as $socialLink)
                        <a href="{{ $socialLink->url }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $socialLink->network_label }} (abre en una pestaña nueva)" class="focus-cyan rounded-sm text-white/60 transition hover:text-cyan">
                            @include('components.social-icon', ['icon' => $socialLink->icon])
                        </a>
                    @endforeach
                @endif
            </nav>
        </div>
    </header>
    <main>@yield('content')</main>
    <footer class="border-t border-white/10 px-5 py-6 text-center mono-label text-white/40">
        @if (config('social.location') === 'footer' && $socialLinks->isNotEmpty())
            <nav aria-label="Redes sociales" class="mb-5 flex flex-wrap items-center justify-center gap-4">
                @foreach ($socialLinks as $socialLink)
                    <a href="{{ $socialLink->url }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $socialLink->network_label }} (abre en una pestaña nueva)" class="focus-cyan rounded-sm text-white/60 transition hover:text-cyan">
                        @include('components.social-icon', ['icon' => $socialLink->icon])
                    </a>
                @endforeach
            </nav>
        @endif
        @if ($siteSettings->description)<p class="mx-auto mb-4 max-w-3xl normal-case leading-6">{{ $siteSettings->description }}</p>@endif
        <div class="mb-4 flex flex-wrap items-center justify-center gap-x-5 gap-y-2 normal-case">
            @if ($siteSettings->contact_email)<a class="hover:text-cyan" href="mailto:{{ $siteSettings->contact_email }}">{{ $siteSettings->contact_email }}</a>@endif
            @if ($siteSettings->phone)<a class="hover:text-cyan" href="tel:{{ preg_replace('/[^0-9+]/', '', $siteSettings->phone) }}">{{ $siteSettings->phone }}</a>@endif
            @if ($siteSettings->address)<span>{{ $siteSettings->address }}</span>@endif
        </div>
        @if ($siteSettings->footer_text)<p class="mb-3 normal-case">{{ $siteSettings->footer_text }}</p>@endif
        <p>© {{ now()->timezone($siteSettings->presentation_timezone)->format('Y') }} {{ $siteSettings->site_name }}</p>
    </footer>
</body>
</html>
