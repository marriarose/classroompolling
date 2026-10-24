@extends('layouts.app')
@section('title', $classroom->name)
@section('content')
<x-card class="text-center">
    <h3>{{ $classroom->name }}</h3>
    <p class="text-muted mb-1">Instructor: {{ $classroom->faculty->name }}</p>
    <p>Participants: <strong id="participants">{{ $participants }}</strong></p>
    @if ($activeQuiz)
        <div class="alert alert-success">A quiz is live!</div>
        <a href="{{ route('student.quizzes.show', $activeQuiz) }}" class="btn btn-success btn-lg">Open "{{ $activeQuiz->title }}"</a>
    @else
        <div class="spinner-border text-primary mb-3" role="status"></div>
        <p class="fs-5 mb-0">Waiting for quiz to start...</p>
    @endif
</x-card>
@if ($closedQuizzes->isNotEmpty())
<x-card title="Finished quizzes">
    @foreach ($closedQuizzes as $q)
        <a class="d-block py-2 border-bottom" href="{{ route('student.quizzes.result', $q) }}">{{ $q->title }} – view my score</a>
    @endforeach
</x-card>
@endif
@endsection
@push('scripts')
<script>
startPolling(@json(route('student.classrooms.status', $classroom)), function (d) {
    document.getElementById('participants').textContent = d.participants;
    @if (! $activeQuiz) if (d.active_quiz_url) location.href = d.active_quiz_url; @endif
}, 3000);
</script>
@endpush
