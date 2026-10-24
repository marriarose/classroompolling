@extends('layouts.app')
@section('title', 'My result')
@section('content')
<x-card class="text-center">
    <p class="text-muted mb-1">{{ $quiz->classroom->name }}</p>
    <h3>{{ $quiz->title }}</h3>
    @if ($row)
        <div class="display-3 fw-bold my-3">{{ $row['points'] }} / {{ $row['total_points'] }}</div>
        <div class="fs-4">{{ $row['percentage'] }}%</div>
        <p class="mt-3 mb-0">{{ $row['correct'] }} of {{ $row['total_questions'] }} correct · Rank
            <span class="badge bg-warning text-dark fs-6">#{{ $row['rank'] }}</span></p>
    @else
        <p class="text-muted">No score available.</p>
    @endif
    <a href="{{ route('student.classrooms.show', $quiz->classroom) }}" class="btn btn-outline-primary mt-4">Back to classroom</a>
</x-card>
@endsection
