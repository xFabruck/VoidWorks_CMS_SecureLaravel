<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#08090e">
    <title>@yield('title', 'Acceso') · {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="site-shell">
    <header class="border-b border-white/10 bg-black/40 backdrop-blur-xl">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-5 py-4 lg:px-10">
            <a href="{{ route('home') }}" class="focus-cyan flex items-center gap-3 rounded-sm">
                <img src="{{ asset('images/voidworks-emblem.svg') }}" alt="" class="h-9 w-9">
                <span class="font-display text-sm font-semibold tracking-[.18em]">VOIDWORKS <span class="mono-label text-white/45">// cms auth</span></span>
            </a>
            <a href="{{ route('home') }}" class="focus-cyan mono-label rounded-sm px-2 py-2 text-white/60 transition hover:text-cyan">← VOLVER AL SITIO</a>
        </div>
    </header>
    <main>@yield('content')</main>
    <footer class="border-t border-white/10 px-5 py-6 text-center mono-label text-white/40">ACCESO ADMINISTRATIVO <span class="mx-2 text-cyan">//</span> {{ config('app.name') }}</footer>
</body>
</html>
