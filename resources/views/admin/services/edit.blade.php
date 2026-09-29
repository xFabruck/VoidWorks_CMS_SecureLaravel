@extends('layouts.admin')

@section('title', 'Editar servicio')
@section('heading', 'Editar servicio')
@section('breadcrumb', 'CONTENIDO / SERVICIOS / EDITAR')

@section('content')
    <div class="mx-auto max-w-4xl"><a href="{{ route('admin.services.index') }}" class="focus-cyan mono-label text-white/55 hover:text-cyan">← VOLVER A SERVICIOS</a><section class="glass-panel mt-5 rounded-md p-5 sm:p-8"><p class="mono-label text-cyan">CONFIGURACIÓN // SERVICIOS</p><h2 class="mt-2 font-display text-2xl font-semibold text-white">Editar servicio</h2>@include('admin.services._form')</section></div>
@endsection
