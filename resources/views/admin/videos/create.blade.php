@extends('layouts.admin')

@section('title', 'Agregar video')
@section('breadcrumb', 'CONTENIDO / VIDEOS / NUEVO')

@section('content')
    <div class="mx-auto max-w-4xl">
        <section class="glass-panel rounded-md p-5 sm:p-8">
            <p class="mono-label text-cyan">// NUEVO ELEMENTO</p>
            <h1 class="mt-2 font-display text-2xl font-semibold text-white">Agregar <span class="text-cyan">video</span></h1>
            @include('admin.videos._form')
        </section>
    </div>
@endsection
