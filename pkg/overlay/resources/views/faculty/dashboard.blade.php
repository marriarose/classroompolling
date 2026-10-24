@extends('layouts.app')
@section('title', 'Faculty Dashboard')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h2 class="mb-0">Welcome, {{ auth()->user()->name }}</h2>
    <a href="{{ route('faculty.classrooms.create') }}" class="btn btn-primary btn-lg">+ Create Classroom</a>
</div>
<x-card title="Active Classrooms">
    @forelse ($classrooms as $c)
        <div class="d-flex flex-wrap justify-content-between align-items-center border-bottom py-3 gap-2">
            <div>
                <div class="fw-semibold fs-5">{{ $c->name }}</div>
                <span class="join-code text-primary">{{ $c->join_code }}</span>
                <span class="text-muted ms-2">{{ $c->students_count }} {{ \Illuminate\Support\Str::plural('student', $c->students_count) }}</span>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a class="btn btn-outline-primary btn-sm" href="{{ route('faculty.classrooms.show', $c) }}">View Room</a>
                <a class="btn btn-outline-primary btn-sm" href="{{ route('faculty.classrooms.show', $c) }}#students">Students</a>
                <a class="btn btn-outline-primary btn-sm" href="{{ route('faculty.classrooms.show', $c) }}#new-quiz">Create Quiz</a>
                <a class="btn btn-outline-primary btn-sm" href="{{ route('faculty.classrooms.show', $c) }}#quizzes">Start Quiz</a>
                <a class="btn btn-outline-secondary btn-sm" href="{{ route('faculty.results.index') }}">Results</a>
            </div>
        </div>
    @empty
        <p class="text-muted mb-0">No active classrooms yet. Create one to get a join code for your students.</p>
    @endforelse
</x-card>
@endsection
