@extends('layouts.admin')

@section('title', 'Editar testimonio')
@section('breadcrumb', 'CONTENIDO / TESTIMONIOS / EDITAR')

@section('content')
    <div class="mx-auto max-w-4xl"><section class="glass-panel rounded-md p-5 sm:p-8"><p class="mono-label text-cyan">// ACTUALIZAR RESEÑA</p><h1 class="mt-2 font-display text-2xl font-semibold text-white">Editar <span class="text-cyan">testimonio</span></h1>@include('admin.testimonials._form')</section></div>
@endsection
