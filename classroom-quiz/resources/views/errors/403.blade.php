@extends('layouts.app')
@section('title', 'Not authorized')
@section('content')
    <x-card class="text-center">
        <h3>Access denied</h3>
        <p class="text-muted">{{ $exception->getMessage() ?: 'You are not authorized to access this resource.' }}</p>
        @auth <a href="{{ route('dashboard') }}" class="btn btn-primary">Back to dashboard</a> @endauth
    </x-card>
@endsection
