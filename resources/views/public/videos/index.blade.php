@extends('layouts.public')

@section('title', 'Videos')
@section('meta_description', 'Videos de '.config('app.name').'.')

@section('content')
    <section class="px-5 py-16 sm:py-20 lg:px-10">
        <div class="mx-auto max-w-7xl">
            <p class="mono-label text-cyan">// CONTENIDO AUDIOVISUAL</p>
            <h1 class="mt-3 font-display text-4xl font-bold tracking-tight text-white sm:text-5xl">Videos</h1>
            <p class="mt-4 max-w-2xl text-base leading-7 text-muted">Explora los videos del estudio.</p>
            @if ($videos->isNotEmpty())
                <div class="mt-10 grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($videos as $video)
                        @if ($video->embed_url)
                            <article class="glass-panel overflow-hidden rounded-md">
                                <div class="aspect-video bg-black">
                                    <iframe src="{{ $video->embed_url }}" title="{{ $video->title }}" loading="lazy" referrerpolicy="strict-origin-when-cross-origin" sandbox="allow-scripts allow-same-origin allow-presentation" allow="accelerometer; encrypted-media; picture-in-picture; fullscreen" allowfullscreen class="h-full w-full" aria-label="Reproductor: {{ $video->title }}"></iframe>
                                </div>
                                <div class="p-5 sm:p-6">
                                    <p class="mono-label text-cyan">{{ $video->provider }}</p>
                                    <h2 class="mt-2 font-display text-xl font-semibold text-white">{{ $video->title }}</h2>
                                    @if ($video->description)<p class="mt-3 whitespace-pre-line text-sm leading-6 text-muted">{{ $video->description }}</p>@endif
                                </div>
                            </article>
                        @endif
                    @endforeach
                </div>
            @else
                <div class="glass-panel mt-10 rounded-md px-6 py-14 text-center"><p class="mono-label text-white/40">GALERÍA // EN PREPARACIÓN</p><p class="mt-3 text-sm text-muted">Pronto compartiremos videos del estudio.</p></div>
            @endif
        </div>
    </section>
@endsection
