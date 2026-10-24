@extends('layouts.app')
@section('title', 'Create Classroom')
@section('content')
<x-card title="Create classroom" style="max-width:560px">
    <form method="POST" action="{{ route('faculty.classrooms.store') }}">@csrf
        <div class="mb-3"><label class="form-label">Classroom name</label>
            <input name="name" value="{{ old('name') }}" class="form-control form-control-lg" placeholder="e.g. AI Fundamentals" required autofocus></div>
        <button class="btn btn-primary btn-lg">Create &amp; generate join code</button>
    </form>
</x-card>
@endsection
