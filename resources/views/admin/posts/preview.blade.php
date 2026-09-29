@extends('layouts.public')

@section('title', 'Vista previa — '.$post->title)

@section('content')
    <div class="border-b border-cyan/20 bg-cyan/5 px-5 py-3 text-center mono-label text-cyan">VISTA PREVIA PRIVADA <a href="{{ route('admin.posts.index') }}" class="focus-cyan ml-4 underline underline-offset-4">VOLVER A PUBLICACIONES</a></div>
    @include('public.posts._article', ['post' => $post])
@endsection
