@extends('layouts.admin')

@section('title', 'Editar categoría')
@section('heading', 'Editar categoría')
@section('breadcrumb', 'CONTENIDO / CATEGORÍAS / EDITAR')

@section('content')
    <div class="mx-auto max-w-3xl"><a href="{{ route('admin.categories.index') }}" class="focus-cyan mono-label text-white/55 hover:text-cyan">← VOLVER A CATEGORÍAS</a><section class="glass-panel mt-5 rounded-md p-5 sm:p-8"><p class="mono-label text-cyan">CONFIGURACIÓN // CATEGORÍAS</p><h2 class="mt-2 font-display text-2xl font-semibold text-white">Editar categoría</h2>@include('admin.categories._form')</section></div>
@endsection
