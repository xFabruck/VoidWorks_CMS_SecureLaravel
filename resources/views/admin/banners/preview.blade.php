@extends('layouts.public')

@section('title', 'Vista previa del banner')

@section('content')
    <div class="border-b border-cyan/20 bg-cyan/5 px-5 py-3 text-center mono-label text-cyan">
        VISTA PREVIA PRIVADA
        <a href="{{ route('admin.banners.index') }}" class="focus-cyan ml-4 underline underline-offset-4">VOLVER AL DASHBOARD</a>
    </div>
    @include('banners._hero', ['banners' => $banners, 'isPreview' => $isPreview])
@endsection
