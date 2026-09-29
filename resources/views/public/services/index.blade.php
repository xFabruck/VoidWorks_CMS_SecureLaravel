@extends('layouts.public')

@section('title', 'Servicios')
@section('meta_description', 'Explora los servicios de '.config('app.name').'.')

@section('content')
    <section class="px-5 py-16 sm:py-20 lg:px-10">
        <div class="mx-auto max-w-7xl">
            <p class="mono-label text-cyan">01 // LO QUE HACEMOS</p>
            <h1 class="mt-3 font-display text-4xl font-bold tracking-tight text-white sm:text-5xl">Servicios</h1>
            <p class="mt-4 max-w-2xl text-base leading-7 text-muted">Conoce las capacidades y servicios de nuestro estudio.</p>
            @if ($services->isNotEmpty())
                <div class="mt-10 grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($services as $service)
                        <article class="glass-panel flex h-full flex-col rounded-md p-5 transition hover:border-cyan/30 sm:p-6">
                            @if ($service->image)<img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($service->image) }}" alt="" class="mb-5 aspect-[16/9] w-full rounded-sm border border-white/10 object-cover">
                            @else<div class="mb-5 grid aspect-[16/9] place-items-center rounded-sm border border-cyan/15 bg-gradient-to-br from-cyan/[.08] to-violet/[.08]"><span class="font-mono text-sm text-cyan">{{ $service->icon ?: 'VW' }}</span></div>@endif
                            <p class="mono-label text-cyan">{{ str_pad((string) $service->position, 2, '0', STR_PAD_LEFT) }} // SERVICIO</p>
                            <h2 class="mt-3 font-display text-xl font-semibold text-white">{{ $service->name }}</h2>
                            <p class="mt-3 flex-1 text-sm leading-6 text-muted">{{ $service->short_description }}</p>
                            <a href="{{ route('services.show', $service->slug) }}" class="focus-cyan mt-6 inline-flex items-center gap-2 self-start font-mono text-xs tracking-wider text-cyan hover:text-white">CONOCER SERVICIO <span aria-hidden="true">→</span></a>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="glass-panel mt-10 rounded-md px-6 py-14 text-center"><p class="mono-label text-white/40">CATÁLOGO // EN PREPARACIÓN</p><p class="mt-3 text-sm text-muted">Pronto compartiremos más información sobre nuestros servicios.</p></div>
            @endif
        </div>
    </section>
@endsection
