@extends('layouts.admin')

@section('title', 'Editar miembro')
@section('breadcrumb', 'CONTENIDO / EQUIPO / EDITAR')

@section('content')
    <div class="mx-auto max-w-4xl"><section class="glass-panel rounded-md p-5 sm:p-8"><p class="mono-label text-cyan">// ACTUALIZAR PERFIL</p><h1 class="mt-2 font-display text-2xl font-semibold text-white">Editar <span class="text-cyan">miembro</span></h1>@include('admin.team._form')</section></div>
@endsection
