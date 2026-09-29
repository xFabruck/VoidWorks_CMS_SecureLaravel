@extends('layouts.public')

@section('title', $service->name)
@section('seo_title', $service->seo_title ?? '')
@section('seo_description', $service->meta_description ?? '')

@section('content')
    <article class="px-5 py-16 sm:py-20 lg:px-10">
        <div class="mx-auto max-w-6xl">
            <a href="{{ route('services.index') }}" class="focus-cyan mono-label text-white/55 hover:text-cyan">← TODOS LOS SERVICIOS</a>
            <div class="mt-7 grid gap-10 lg:grid-cols-[1fr_.9fr] lg:items-start">
                <div>
                    <p class="mono-label text-cyan">SERVICIO // {{ str_pad((string) $service->position, 2, '0', STR_PAD_LEFT) }}</p>
                    <h1 class="mt-4 font-display text-4xl font-bold tracking-tight text-white sm:text-5xl">{{ $service->name }}</h1>
                    <p class="mt-5 font-display text-xl leading-8 text-white/70">{{ $service->short_description }}</p>
                    <div class="mt-7 whitespace-pre-line text-sm leading-8 text-muted">{{ $service->description }}</div>
                    @if ($service->button_text && $service->button_url)<a href="{{ $service->button_url }}" class="focus-cyan mt-8 inline-flex items-center gap-3 rounded-sm bg-cyan px-6 py-4 font-mono text-xs font-semibold tracking-wider text-void transition hover:brightness-110">{{ $service->button_text }} <span aria-hidden="true">↗</span></a>@endif
                </div>
                @if ($service->image)<div class="overflow-hidden rounded-md border border-white/10 bg-panel"><img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($service->image) }}" alt="" class="max-h-[36rem] w-full object-cover"></div>
                @else<div class="grid aspect-square max-h-[36rem] place-items-center rounded-md border border-cyan/15 bg-gradient-to-br from-cyan/[.07] to-violet/[.1]"><span class="font-mono text-2xl text-cyan">{{ $service->icon ?: 'VW' }}</span></div>@endif
            </div>
        </div>
    </article>
@endsection
