@extends('layouts.public')

@section('title', 'Noticias')
@section('meta_description', 'Noticias y publicaciones de '.config('app.name').'.')

@section('content')
    <section class="px-5 py-16 sm:py-20 lg:px-10">
        <div class="mx-auto max-w-7xl">
            <p class="mono-label text-cyan">PUBLICACIONES // ACTUALIDAD</p>
            <h1 class="mt-3 font-display text-4xl font-bold tracking-tight text-white sm:text-5xl">Noticias</h1>
            @if ($posts->isNotEmpty())
                <div class="mt-10 grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($posts as $post)
                        <article class="glass-panel flex h-full flex-col overflow-hidden rounded-md">
                            @if ($post->featured_image)<a href="{{ route('news.show', $post->slug) }}" class="focus-cyan block"><img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($post->featured_image) }}" alt="" class="aspect-[16/9] w-full object-cover"></a>@endif
                            <div class="flex flex-1 flex-col p-5 sm:p-6">
                                <div class="flex flex-wrap items-center gap-3 mono-label text-white/45"><time datetime="{{ $post->published_at->timezone($siteSettings->presentation_timezone)->toIso8601String() }}">{{ $post->published_at->timezone($siteSettings->presentation_timezone)->format('d/m/Y') }}</time>@if ($post->category)<span class="text-cyan">{{ $post->category->name }}</span>@endif</div>
                                <h2 class="mt-3 font-display text-xl font-semibold text-white"><a href="{{ route('news.show', $post->slug) }}" class="focus-cyan rounded-sm hover:text-cyan">{{ $post->title }}</a></h2>
                                @if ($post->excerpt)<p class="mt-3 flex-1 whitespace-pre-line text-sm leading-6 text-muted">{{ $post->excerpt }}</p>@endif
                                <p class="mt-5 text-xs text-white/45">{{ $post->author?->name }}</p>
                            </div>
                        </article>
                    @endforeach
                </div>
                @if ($posts->hasPages())<div class="mt-10">{{ $posts->links() }}</div>@endif
            @else
                <div class="glass-panel mt-10 rounded-md px-6 py-14 text-center"><p class="mono-label text-white/40">NOTICIAS // SIN PUBLICACIONES</p><p class="mt-3 text-sm text-muted">No hay noticias publicadas en este momento.</p></div>
            @endif
        </div>
    </section>
@endsection
