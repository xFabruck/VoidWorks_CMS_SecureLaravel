@extends('layouts.public')

@section('title', 'Inicio')

@section('content')
    @include('banners._hero', ['banners' => $banners, 'isPreview' => $isPreview])

    <section id="estudio" class="border-t border-white/10 px-5 py-14 lg:px-10">
        <div class="mx-auto flex max-w-7xl flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div><p class="mono-label text-cyan">01 // EL ESTUDIO</p><h2 class="mt-3 font-display text-2xl font-semibold text-white sm:text-3xl">Ideas que abren nuevos mundos.</h2></div>
            <p class="max-w-xl text-sm leading-7 text-muted">Voidworks es un estudio independiente enfocado en crear experiencias digitales originales. Este espacio crecerá junto con el proyecto.</p>
        </div>
    </section>
@endsection
