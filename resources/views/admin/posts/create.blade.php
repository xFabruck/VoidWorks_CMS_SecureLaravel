@extends('layouts.admin')

@section('title', 'Nueva publicación')
@section('heading', 'Nueva publicación')
@section('breadcrumb', 'CONTENIDO / PUBLICACIONES / NUEVA')

@section('content')
    <div class="mx-auto max-w-5xl"><a href="{{ route('admin.posts.index') }}" class="focus-cyan mono-label text-white/55 hover:text-cyan">← VOLVER A PUBLICACIONES</a><section class="glass-panel mt-5 rounded-md p-5 sm:p-8"><p class="mono-label text-cyan">EDITORIAL // NUEVA ENTRADA</p><h2 class="mt-2 font-display text-2xl font-semibold text-white">Crear publicación</h2>@include('admin.posts._form')</section></div>
@endsection
