@extends('layouts.app')
@section('title', 'Register')
@section('content')
<x-card title="Create account">
    <form method="POST" action="{{ route('register') }}">@csrf
        <div class="mb-3"><label class="form-label">Full name</label>
            <input name="name" value="{{ old('name') }}" class="form-control form-control-lg" required></div>
        <div class="mb-3"><label class="form-label">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" class="form-control form-control-lg" required></div>
        <div class="mb-3"><label class="form-label">Password (min 8)</label>
            <input type="password" name="password" class="form-control form-control-lg" required></div>
        <div class="mb-3"><label class="form-label">Confirm password</label>
            <input type="password" name="password_confirmation" class="form-control form-control-lg" required></div>
        <div class="mb-3">
            <label class="form-label d-block">I am a</label>
            <div class="btn-group w-100">
                <input type="radio" class="btn-check" name="role" id="r-student" value="student" {{ old('role', 'student') === 'student' ? 'checked' : '' }}>
                <label class="btn btn-outline-primary" for="r-student">Student</label>
                <input type="radio" class="btn-check" name="role" id="r-faculty" value="faculty" {{ old('role') === 'faculty' ? 'checked' : '' }}>
                <label class="btn btn-outline-primary" for="r-faculty">Faculty</label>
            </div>
        </div>
        <button class="btn btn-primary btn-lg w-100">Register</button>
    </form>
    <p class="text-center mt-3 mb-0">Already registered? <a href="{{ route('login') }}">Sign in</a></p>
</x-card>
@endsection
