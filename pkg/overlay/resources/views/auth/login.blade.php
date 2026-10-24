@extends('layouts.app')
@section('title', 'Login')
@section('content')
<x-card title="Sign in">
    <form method="POST" action="{{ route('login') }}">@csrf
        <div class="mb-3"><label class="form-label">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" class="form-control form-control-lg" required autofocus></div>
        <div class="mb-3"><label class="form-label">Password</label>
            <input type="password" name="password" class="form-control form-control-lg" required></div>
        <div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="remember" id="remember"><label class="form-check-label" for="remember">Remember me</label></div>
        <button class="btn btn-primary btn-lg w-100">Login</button>
    </form>
    <p class="text-center mt-3 mb-0">New here? <a href="{{ route('register') }}">Create an account</a></p>
</x-card>
@endsection
