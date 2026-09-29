@extends('layouts.admin')

@section('title', 'Editar publicación')
@section('heading', 'Editar publicación')
@section('breadcrumb', 'CONTENIDO / PUBLICACIONES / EDITAR')

@section('content')
    <div class="mx-auto max-w-5xl"><a href="{{ route('admin.posts.index') }}" class="focus-cyan mono-label text-white/55 hover:text-cyan">← VOLVER A PUBLICACIONES</a><section class="glass-panel mt-5 rounded-md p-5 sm:p-8"><p class="mono-label text-cyan">EDITORIAL // EDICIÓN</p><h2 class="mt-2 font-display text-2xl font-semibold text-white">Editar publicación</h2>@include('admin.posts._form')</section></div>
@endsection
