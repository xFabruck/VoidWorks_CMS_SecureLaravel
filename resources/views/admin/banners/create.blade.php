@extends('layouts.admin')

@section('title', 'Nuevo banner')
@section('heading', 'Nuevo banner')

@section('content')
    <div class="mx-auto max-w-4xl">
        <a href="{{ route('admin.banners.index') }}" class="focus-cyan mono-label text-white/55 hover:text-cyan">← VOLVER A BANNERS</a>
        <section class="glass-panel mt-5 rounded-md p-5 sm:p-8">
            <p class="mono-label text-cyan">CONFIGURACIÓN // HERO</p>
            <h2 class="mt-2 font-display text-2xl font-semibold text-white">Crear banner</h2>
            @include('admin.banners._form')
        </section>
    </div>
@endsection
