@extends('layouts.public')

@section('title', 'Testimonios')
@section('meta_description', 'Opiniones de clientes de '.config('app.name').'.')

@section('content')
    <section class="px-5 py-16 sm:py-20 lg:px-10">
        <div class="mx-auto max-w-7xl">
            <p class="mono-label text-cyan">// EXPERIENCIAS COMPARTIDAS</p>
            <h1 class="mt-3 font-display text-4xl font-bold tracking-tight text-white sm:text-5xl">Testimonios</h1>
            <p class="mt-4 max-w-2xl text-base leading-7 text-muted">Lo que nuestros clientes comparten sobre su experiencia.</p>
            @if ($testimonials->isNotEmpty())
                <div class="mt-10 grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($testimonials as $item)
                        <article class="glass-panel flex h-full flex-col rounded-md p-5 sm:p-6">
                            @if ($item->rating)<p class="mono-label text-amber-300" aria-label="{{ $item->rating }} de 5 estrellas">{{ str_repeat('★', $item->rating) }}<span class="sr-only">, {{ $item->rating }} de 5 estrellas</span></p>@endif
                            <blockquote class="mt-4 flex-1 whitespace-pre-line text-sm leading-7 text-muted">“{{ $item->testimonial }}”</blockquote>
                            <div class="mt-6 flex items-center gap-3 border-t border-white/10 pt-5">
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($item->photo) }}" alt="" loading="lazy" class="h-12 w-12 rounded-full border border-white/10 object-cover">
                                <div><p class="font-display font-semibold text-white">{{ $item->name }}</p><p class="mt-1 text-xs text-white/50">{{ $item->position_or_company }}</p></div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="glass-panel mt-10 rounded-md px-6 py-14 text-center"><p class="mono-label text-white/40">TESTIMONIOS // EN PREPARACIÓN</p><p class="mt-3 text-sm text-muted">Pronto compartiremos experiencias de nuestros clientes.</p></div>
            @endif
        </div>
    </section>
@endsection
