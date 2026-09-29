@extends('layouts.admin')
@section('title', 'Crear usuario')
@section('breadcrumb', 'ADMINISTRACIÓN / USUARIOS')
@section('content')
    @include('admin.users.form', ['editing' => false])
@endsection
