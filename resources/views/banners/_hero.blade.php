@if ($banners->isNotEmpty())
    <section @if ($banners->count() > 1 && ! $isPreview) data-hero-carousel aria-roledescription="carousel" aria-label="Banners destacados" @endif class="relative isolate overflow-hidden px-5 py-20 sm:py-28 lg:px-10 lg:py-36">
        <div aria-hidden="true" class="absolute -right-32 top-0 -z-10 h-96 w-96 rounded-full bg-violet/20 blur-[130px]"></div>
        <div aria-hidden="true" class="absolute -left-40 bottom-0 -z-10 h-96 w-96 rounded-full bg-cyan/10 blur-[130px]"></div>

        @foreach ($banners as $banner)
            <article data-hero-slide role="group" aria-roledescription="diapositiva" aria-label="{{ $loop->iteration }} de {{ $banners->count() }}" @if (! $loop->first) hidden @endif class="mx-auto grid max-w-7xl items-center gap-12 lg:grid-cols-[1.2fr_.8fr] lg:gap-16">
                <div>
                    @if ($isPreview)
                        <p class="mono-label inline-flex items-center gap-2 rounded-full border border-cyan/20 bg-cyan/5 px-4 py-2 text-cyan-soft">VISTA PREVIA // NO PUBLICADA</p>
                    @endif
                    <h1 class="mt-6 max-w-4xl font-display text-5xl font-bold leading-[1.03] tracking-[-.05em] text-white sm:text-6xl lg:text-7xl">{{ $banner->title }}</h1>
                    @if ($banner->subtitle)
                        <p class="mt-5 max-w-3xl font-display text-2xl font-medium leading-tight tracking-[-.03em] text-white/75 sm:text-3xl">{{ $banner->subtitle }}</p>
                    @endif
                    @if ($banner->description)
                        <p class="mt-7 max-w-2xl text-base leading-8 text-muted sm:text-lg">{{ $banner->description }}</p>
                    @endif
                    @if ($banner->button_text && $banner->button_url)
                        <a href="{{ $banner->button_url }}" class="focus-cyan mt-9 inline-flex items-center gap-3 rounded-sm bg-cyan px-6 py-4 font-mono text-xs font-semibold tracking-wider text-void transition hover:brightness-110">{{ $banner->button_text }} <span aria-hidden="true">↗</span></a>
                    @endif
                </div>

                <div class="relative mx-auto aspect-square w-full max-w-xl overflow-hidden rounded-md border border-white/10 bg-panel-raised">
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($banner->image) }}" alt="{{ $banner->image_alt }}" class="h-full w-full object-cover">
                    <div aria-hidden="true" class="pointer-events-none absolute inset-0 bg-gradient-to-t from-black/65 via-black/5 to-transparent"></div>
                    <div class="mono-label absolute inset-x-0 bottom-0 flex items-center justify-between gap-3 px-5 py-4 text-white/75"><span>VOIDWORKS // STUDIO</span><span class="text-cyan">{{ str_pad((string) $banner->position, 2, '0', STR_PAD_LEFT) }}</span></div>
                </div>
            </article>
        @endforeach

        @if ($banners->count() > 1 && ! $isPreview)
            <div class="mx-auto mt-8 flex max-w-7xl items-center justify-between gap-4" role="group" aria-label="Controles del carrusel">
                <div class="flex items-center gap-2">
                    <button type="button" data-carousel-previous aria-label="Banner anterior" class="focus-cyan grid h-10 w-10 place-items-center rounded-sm border border-white/15 text-white/75 transition hover:border-cyan/50 hover:text-cyan">←</button>
                    <button type="button" data-carousel-next aria-label="Siguiente banner" class="focus-cyan grid h-10 w-10 place-items-center rounded-sm border border-white/15 text-white/75 transition hover:border-cyan/50 hover:text-cyan">→</button>
                </div>
                <div class="flex items-center gap-2" role="group" aria-label="Elegir banner">
                    @foreach ($banners as $banner)
                        <button type="button" data-carousel-indicator="{{ $loop->index }}" aria-label="Mostrar banner {{ $loop->iteration }}" aria-current="{{ $loop->first ? 'true' : 'false' }}" class="focus-cyan h-2.5 w-2.5 rounded-full border border-cyan/60 transition aria-[current=true]:bg-cyan"></button>
                    @endforeach
                </div>
                <span class="mono-label text-white/45">{{ str_pad((string) $banners->count(), 2, '0', STR_PAD_LEFT) }} DESTACADOS</span>
            </div>
        @endif
    </section>
@endif
