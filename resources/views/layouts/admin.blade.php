<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#08090e">
    <title>@yield('title', 'Dashboard') · {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="site-shell">
    <div class="min-h-screen lg:grid lg:grid-cols-[250px_minmax(0,1fr)]">
        <button type="button" data-admin-menu-backdrop class="fixed inset-0 z-40 hidden bg-black/70 backdrop-blur-sm lg:hidden" aria-label="Cerrar menú"></button>
        <aside id="admin-sidebar" data-admin-sidebar class="fixed inset-y-0 left-0 z-50 flex h-screen w-[min(18rem,85vw)] max-h-screen -translate-x-full flex-col overflow-hidden border-r border-white/10 bg-[#111217] px-4 py-5 transition-transform duration-200 lg:sticky lg:top-0 lg:z-auto lg:h-screen lg:w-auto lg:translate-x-0 lg:border-r lg:px-3">
            <a href="{{ route('admin.dashboard') }}" class="focus-cyan flex items-center gap-3 rounded-sm border-b border-white/10 px-2 pb-5">
                <img src="{{ asset('images/voidworks-emblem.svg') }}" alt="" class="h-9 w-9">
                <span class="font-display text-sm font-semibold tracking-[.13em]">VOIDWORKS <span class="mono-label block text-white/40">CMS // CONTROL</span></span>
            </a>

            <p class="mono-label mt-5 shrink-0 px-2 text-white/35">// SECCIÓN PRINCIPAL</p>
            <nav aria-label="Navegación administrativa" class="mt-2 flex min-h-0 flex-1 flex-col gap-1 overflow-y-auto overscroll-contain pr-1">
                <a href="{{ route('admin.dashboard') }}" @class(['focus-cyan flex items-center gap-3 rounded-sm border px-3 py-2.5 text-sm transition', 'border-cyan/40 bg-cyan text-void' => request()->routeIs('admin.dashboard'), 'border-transparent text-white/65 hover:border-white/10 hover:bg-white/5 hover:text-white' => ! request()->routeIs('admin.dashboard')])>
                    <span aria-hidden="true">▦</span> Dashboard
                </a>
                @can('users.view')
                    <a href="{{ route('admin.users.index') }}" @class(['focus-cyan flex items-center gap-3 rounded-sm border px-3 py-2.5 text-sm transition', 'border-cyan/30 bg-cyan/10 text-cyan' => request()->routeIs('admin.users.*'), 'border-transparent text-white/65 hover:border-white/10 hover:bg-white/5 hover:text-white' => ! request()->routeIs('admin.users.*')])>
                        <span aria-hidden="true">♙</span> Usuarios
                    </a>
                @endcan
                @can('banners.view')<a href="{{ route('admin.banners.index') }}" @class(['focus-cyan flex items-center gap-3 rounded-sm border px-3 py-2.5 text-sm transition', 'border-cyan/30 bg-cyan/10 text-cyan' => request()->routeIs('admin.banners.*'), 'border-transparent text-white/65 hover:border-white/10 hover:bg-white/5 hover:text-white' => ! request()->routeIs('admin.banners.*')])>
                    <span aria-hidden="true">▧</span> Banners / Hero
                </a>@endcan
                @can('services.view')<a href="{{ route('admin.services.index') }}" @class(['focus-cyan flex items-center gap-3 rounded-sm border px-3 py-2.5 text-sm transition', 'border-cyan/30 bg-cyan/10 text-cyan' => request()->routeIs('admin.services.*'), 'border-transparent text-white/65 hover:border-white/10 hover:bg-white/5 hover:text-white' => ! request()->routeIs('admin.services.*')])>
                    <span aria-hidden="true">◇</span> Servicios
                </a>@endcan
                @can('cms.manage-other-modules')
                <a href="{{ route('admin.categories.index') }}" @class(['focus-cyan flex items-center gap-3 rounded-sm border px-3 py-2.5 text-sm transition', 'border-cyan/30 bg-cyan/10 text-cyan' => request()->routeIs('admin.categories.*'), 'border-transparent text-white/65 hover:border-white/10 hover:bg-white/5 hover:text-white' => ! request()->routeIs('admin.categories.*')])>
                    <span aria-hidden="true">▱</span> Categorías
                </a>
                @endcan
                @can('posts.view')
                <a href="{{ route('admin.posts.index') }}" @class(['focus-cyan flex items-center gap-3 rounded-sm border px-3 py-2.5 text-sm transition', 'border-cyan/30 bg-cyan/10 text-cyan' => request()->routeIs('admin.posts.*'), 'border-transparent text-white/65 hover:border-white/10 hover:bg-white/5 hover:text-white' => ! request()->routeIs('admin.posts.*')])>
                    <span aria-hidden="true">▤</span> Publicaciones / Noticias
                </a>
                @endcan
                @can('cms.manage-other-modules')
                <a href="{{ route('admin.about.edit') }}" @class(['focus-cyan flex items-center gap-3 rounded-sm border px-3 py-2.5 text-sm transition', 'border-cyan/30 bg-cyan/10 text-cyan' => request()->routeIs('admin.about.*'), 'border-transparent text-white/65 hover:border-white/10 hover:bg-white/5 hover:text-white' => ! request()->routeIs('admin.about.*')])>
                    <span aria-hidden="true">◎</span> Nosotros / Institucional
                </a>
                @endcan
                @can('media.view')
                <a href="{{ route('admin.media.index') }}" @class(['focus-cyan flex items-center gap-3 rounded-sm border px-3 py-2.5 text-sm transition', 'border-cyan/30 bg-cyan/10 text-cyan' => request()->routeIs('admin.media.*'), 'border-transparent text-white/65 hover:border-white/10 hover:bg-white/5 hover:text-white' => ! request()->routeIs('admin.media.*')])>
                    <span aria-hidden="true">▧</span> Multimedia
                </a>
                @endcan
                @can('cms.manage-other-modules')
                <a href="{{ route('admin.videos.index') }}" @class(['focus-cyan flex items-center gap-3 rounded-sm border px-3 py-2.5 text-sm transition', 'border-cyan/30 bg-cyan/10 text-cyan' => request()->routeIs('admin.videos.*'), 'border-transparent text-white/65 hover:border-white/10 hover:bg-white/5 hover:text-white' => ! request()->routeIs('admin.videos.*')])>
                    <span aria-hidden="true">▶</span> Videos
                </a>
                <a href="{{ route('admin.team.index') }}" @class(['focus-cyan flex items-center gap-3 rounded-sm border px-3 py-2.5 text-sm transition', 'border-cyan/30 bg-cyan/10 text-cyan' => request()->routeIs('admin.team.*'), 'border-transparent text-white/65 hover:border-white/10 hover:bg-white/5 hover:text-white' => ! request()->routeIs('admin.team.*')])>
                    <span aria-hidden="true">♙</span> Equipo
                </a>
                <a href="{{ route('admin.testimonials.index') }}" @class(['focus-cyan flex items-center gap-3 rounded-sm border px-3 py-2.5 text-sm transition', 'border-cyan/30 bg-cyan/10 text-cyan' => request()->routeIs('admin.testimonials.*'), 'border-transparent text-white/65 hover:border-white/10 hover:bg-white/5 hover:text-white' => ! request()->routeIs('admin.testimonials.*')])>
                    <span aria-hidden="true">❝</span> Testimonios
                </a>
                <a href="{{ route('admin.contact-messages.index') }}" @class(['focus-cyan flex items-center gap-3 rounded-sm border px-3 py-2.5 text-sm transition', 'border-cyan/30 bg-cyan/10 text-cyan' => request()->routeIs('admin.contact-messages.*'), 'border-transparent text-white/65 hover:border-white/10 hover:bg-white/5 hover:text-white' => ! request()->routeIs('admin.contact-messages.*')])>
                    <span aria-hidden="true">✉</span> Contacto
                </a>
                @endcan
                @can('settings.view')<a href="{{ route('admin.social-links.index') }}" @class(['focus-cyan flex items-center gap-3 rounded-sm border px-3 py-2.5 text-sm transition', 'border-cyan/30 bg-cyan/10 text-cyan' => request()->routeIs('admin.social-links.*'), 'border-transparent text-white/65 hover:border-white/10 hover:bg-white/5 hover:text-white' => ! request()->routeIs('admin.social-links.*')])>
                    <span aria-hidden="true">◎</span> Redes sociales
                </a>@endcan
                @can('settings.view')
                <a href="{{ route('admin.settings.edit') }}" @class(['focus-cyan flex items-center gap-3 rounded-sm border px-3 py-2.5 text-sm transition', 'border-cyan/30 bg-cyan/10 text-cyan' => request()->routeIs('admin.settings.*'), 'border-transparent text-white/65 hover:border-white/10 hover:bg-white/5 hover:text-white' => ! request()->routeIs('admin.settings.*')])>
                    <span aria-hidden="true">⚙</span> Configuración general
                </a>
                <a href="{{ route('admin.settings.mail.edit') }}" @class(['focus-cyan flex items-center gap-3 rounded-sm border px-3 py-2.5 text-sm transition', 'border-cyan/30 bg-cyan/10 text-cyan' => request()->routeIs('admin.settings.mail.*'), 'border-transparent text-white/65 hover:border-white/10 hover:bg-white/5 hover:text-white' => ! request()->routeIs('admin.settings.mail.*')])>
                    <span aria-hidden="true">✉</span> Correo
                </a>
                <a href="{{ route('admin.seo.edit') }}" @class(['focus-cyan flex items-center gap-3 rounded-sm border px-3 py-2.5 text-sm transition', 'border-cyan/30 bg-cyan/10 text-cyan' => request()->routeIs('admin.seo.*'), 'border-transparent text-white/65 hover:border-white/10 hover:bg-white/5 hover:text-white' => ! request()->routeIs('admin.seo.*')])>
                    <span aria-hidden="true">⌕</span> Configuración SEO
                </a>
                @endcan
                @can('audit.view')
                <a href="{{ route('admin.audit.index') }}" @class(['focus-cyan flex items-center gap-3 rounded-sm border px-3 py-2.5 text-sm transition', 'border-cyan/30 bg-cyan/10 text-cyan' => request()->routeIs('admin.audit.*'), 'border-transparent text-white/65 hover:border-white/10 hover:bg-white/5 hover:text-white' => ! request()->routeIs('admin.audit.*')])>
                    <span aria-hidden="true">▤</span> Auditoría
                </a>
                @endcan
            </nav>

            <div class="mt-auto hidden border-t border-white/10 px-2 pt-4 lg:block">
                <p class="mono-label text-white/35">SESIÓN</p>
                <p class="mt-2 truncate text-xs text-white/70">{{ auth()->user()->email }}</p>
                <div class="mt-3 flex items-center gap-2 text-xs text-emerald-300"><span class="h-2 w-2 rounded-full bg-emerald-400"></span> Autenticada</div>
            </div>
        </aside>

        <div class="min-w-0">
            <header class="flex flex-wrap items-center justify-between gap-4 border-b border-white/10 bg-[#111217]/90 px-5 py-3 sm:px-8">
                <div class="flex items-center gap-4">
                    <button type="button" data-admin-menu-toggle class="focus-cyan inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-sm border border-white/15 text-lg text-white/75 transition hover:border-cyan/50 hover:text-cyan lg:hidden" aria-label="Abrir menú" aria-controls="admin-sidebar" aria-expanded="false">
                        <span aria-hidden="true">☰</span>
                    </button>
                    <div>
                        <p class="mono-label text-white/45">VOIDWORKS CMS <span class="mx-1 text-cyan/70">/</span> @yield('breadcrumb', 'DASHBOARD CENTRAL')</p>
                        <p class="mt-1 flex items-center gap-2 text-xs text-white/55"><span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span> Consola autenticada</p>
                    </div>
                </div>
                <div class="flex items-center gap-3 sm:gap-5">
                    <div class="hidden text-right sm:block"><p class="text-sm font-medium text-white">{{ auth()->user()->name }}</p><p class="text-xs text-white/45">{{ auth()->user()->email }}</p></div>
                    <span class="flex h-9 w-9 items-center justify-center rounded-sm border border-cyan/25 bg-cyan/10 font-display text-sm font-semibold text-cyan" aria-hidden="true">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="focus-cyan rounded-sm border border-white/15 px-3 py-2 font-mono text-[.65rem] tracking-wider text-white/70 transition hover:border-cyan/50 hover:text-cyan">CERRAR SESIÓN</button>
                    </form>
                </div>
            </header>
            <main class="p-5 sm:p-8">@yield('content')</main>
        </div>
    </div>
</body>
</html>
