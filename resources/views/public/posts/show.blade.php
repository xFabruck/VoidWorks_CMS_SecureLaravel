@extends('layouts.public')

@section('title', $post->title)
@section('seo_title', $post->seo_title ?? '')
@section('seo_description', $post->meta_description ?? '')
@section('og_type', 'article')

@section('content')
    @include('public.posts._article', ['post' => $post])
@endsection
