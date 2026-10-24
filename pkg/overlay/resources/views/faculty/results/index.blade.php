@extends('layouts.app')
@section('title', 'Results')
@section('content')
<h2 class="mb-3">Previous quiz results</h2>
<x-card>
    <div class="table-responsive"><table class="table align-middle mb-0">
        <thead><tr><th>Quiz</th><th>Classroom</th><th>Ended</th><th></th></tr></thead>
        <tbody>
        @forelse ($quizzes as $quiz)
            <tr><td>{{ $quiz->title }}</td><td>{{ $quiz->classroom->name }}</td>
                <td>{{ $quiz->ended_at?->format('d M Y, H:i') }}</td>
                <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="{{ route('faculty.quizzes.results', $quiz) }}">View</a></td></tr>
        @empty
            <tr><td colspan="4" class="text-muted">No closed quizzes yet.</td></tr>
        @endforelse
        </tbody>
    </table></div>
</x-card>
@endsection
