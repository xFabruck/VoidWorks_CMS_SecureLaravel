@extends('layouts.admin')
@section('title', 'Editar usuario')
@section('breadcrumb', 'ADMINISTRACIÓN / USUARIOS')
@section('content')
    @include('admin.users.form', ['editing' => true])
@endsection
