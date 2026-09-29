@extends('layouts.admin')

@section('title', 'Agregar red social')
@section('breadcrumb', 'CONFIGURACIÓN / REDES SOCIALES / NUEVA')

@section('content')
    <div class="mx-auto max-w-3xl"><section class="glass-panel rounded-md p-5 sm:p-8"><p class="mono-label text-cyan">// NUEVO ENLACE SOCIAL</p><h1 class="mt-2 font-display text-2xl font-semibold text-white">Agregar <span class="text-cyan">red social</span></h1>@include('admin.social-links._form')</section></div>
@endsection
