@extends('layouts.app')
@section('title', 'Student Dashboard')
@section('content')
<h2 class="mb-3">Hi, {{ auth()->user()->name }}</h2>
@include('student.partials.join-form')
<x-card title="Your classrooms">
    @forelse ($classrooms as $c)
        <a href="{{ route('student.classrooms.show', $c) }}" class="d-flex justify-content-between align-items-center text-decoration-none border-bottom py-3">
            <div><div class="fw-semibold">{{ $c->name }}</div><div class="small text-muted">Instructor: {{ $c->faculty->name }}</div></div>
            <span class="badge {{ $c->isActive() ? 'bg-success' : 'bg-secondary' }}">{{ $c->status }}</span>
        </a>
    @empty
        <p class="text-muted mb-0">You haven't joined a classroom yet.</p>
    @endforelse
</x-card>
@endsection
