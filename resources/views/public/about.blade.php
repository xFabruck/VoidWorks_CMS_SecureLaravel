@extends('layouts.public')

@section('title', $aboutPage->title)
@section('meta_description', $aboutPage->subtitle ?: $aboutPage->title)

@section('content')
    <article>
        <section class="relative isolate overflow-hidden border-b border-white/10 px-5 py-16 sm:py-20 lg:px-10 lg:py-28">
            <div aria-hidden="true" class="absolute -right-32 top-0 -z-10 h-96 w-96 rounded-full bg-violet/15 blur-[130px]"></div>
            <div class="mx-auto grid max-w-7xl items-center gap-10 lg:grid-cols-[1fr_.85fr]">
                <div>
                    <p class="mono-label text-cyan">NOSOTROS // {{ config('app.name') }}</p>
                    <h1 class="mt-4 font-display text-4xl font-bold leading-tight tracking-tight text-white sm:text-5xl lg:text-6xl">{{ $aboutPage->title }}</h1>
                    @if ($aboutPage->subtitle)<p class="mt-5 font-display text-xl leading-8 text-white/70">{{ $aboutPage->subtitle }}</p>@endif
                    <div class="mt-7 whitespace-pre-line text-sm leading-8 text-muted sm:text-base">{{ $aboutPage->content }}</div>
                </div>
                @if ($aboutPage->primary_image)
                    <div class="overflow-hidden rounded-md border border-white/10 bg-panel"><img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($aboutPage->primary_image) }}" alt="{{ $aboutPage->title }}" class="max-h-[34rem] w-full object-cover"></div>
                @endif
            </div>
        </section>

        <section class="border-b border-white/10 px-5 py-14 lg:px-10 lg:py-20">
            <div class="mx-auto grid max-w-7xl gap-6 md:grid-cols-3">
                <section class="glass-panel rounded-md p-6"><p class="mono-label text-cyan">01 // MISIÓN</p><div class="mt-4 whitespace-pre-line text-sm leading-7 text-muted">{{ $aboutPage->mission }}</div></section>
                <section class="glass-panel rounded-md p-6"><p class="mono-label text-cyan">02 // VISIÓN</p><div class="mt-4 whitespace-pre-line text-sm leading-7 text-muted">{{ $aboutPage->vision }}</div></section>
                <section class="glass-panel rounded-md p-6"><p class="mono-label text-cyan">03 // VALORES</p><div class="mt-4 whitespace-pre-line text-sm leading-7 text-muted">{{ $aboutPage->values }}</div></section>
            </div>
        </section>

        <section class="px-5 py-14 lg:px-10 lg:py-20">
            <div class="mx-auto grid max-w-7xl items-start gap-10 lg:grid-cols-[1fr_.8fr]">
                <div><p class="mono-label text-cyan">04 // HISTORIA</p><div class="mt-4 whitespace-pre-line text-sm leading-8 text-muted sm:text-base">{{ $aboutPage->history }}</div></div>
                @if ($aboutPage->secondary_image)
                    <div class="overflow-hidden rounded-md border border-white/10 bg-panel"><img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($aboutPage->secondary_image) }}" alt="{{ $aboutPage->title }}" class="max-h-[32rem] w-full object-cover"></div>
                @endif
            </div>
        </section>
    </article>
@endsection
