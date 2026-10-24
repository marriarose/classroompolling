@extends('layouts.app')
@section('title', 'Results – '.$quiz->title)
@section('content')
<a href="{{ route('faculty.classrooms.show', $quiz->classroom) }}" class="small">&larr; {{ $quiz->classroom->name }}</a>
<h2 class="mb-1">Quiz Results: {{ $quiz->title }}
    @if ($quiz->isActive())<span class="badge bg-success fs-6">Live – round in progress</span>@elseif($quiz->isDraft())<span class="badge bg-secondary fs-6">Draft</span>@endif</h2>
<p class="text-muted">{{ $quiz->classroom->name }}</p>

<div class="row g-3 mb-4">
    @foreach ([
        ['Total Students', $stats['total_students']],
        ['Average Score', $stats['average_score'].' / '.$stats['total_points']],
        ['Highest Score', $stats['highest_score'].' / '.$stats['total_points']],
        ['Lowest Score', $stats['lowest_score'].' / '.$stats['total_points']],
        ['Participation', $stats['participation_rate'].'%'],
    ] as [$label, $value])
        <div class="col-6 col-md"><div class="card shadow-sm h-100"><div class="card-body text-center">
            <div class="text-muted small">{{ $label }}</div><div class="fs-4 fw-bold">{{ $value }}</div></div></div></div>
    @endforeach
</div>

<x-card title="Scoreboard">
    <div class="table-responsive"><table class="table align-middle mb-0">
        <thead><tr><th>Rank</th><th>Student</th><th>Correct</th><th>Score</th><th>Percentage</th></tr></thead>
        <tbody>
        @forelse ($rows as $r)
            <tr>
                <td><span class="badge {{ $r['rank'] <= 3 ? 'bg-warning text-dark' : 'bg-light text-dark' }}">{{ $r['rank'] }}</span></td>
                <td>{{ $r['name'] }}</td>
                <td>{{ $r['correct'] }} / {{ $r['total_questions'] }}</td>
                <td>{{ $r['points'] }} / {{ $r['total_points'] }}</td>
                <td style="min-width:140px"><div class="progress" style="height:18px"><div class="progress-bar" style="width: {{ $r['percentage'] }}%">{{ $r['percentage'] }}%</div></div></td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-muted">No students joined this classroom.</td></tr>
        @endforelse
        </tbody>
    </table></div>
</x-card>

<x-card title="Per-question statistics">
    <div class="table-responsive"><table class="table mb-0">
        <thead><tr><th>Question</th><th>Correct</th><th>Incorrect</th></tr></thead>
        <tbody>
        @foreach ($questions as $q)
            <tr><td>{{ $q['question'] }}</td><td class="text-success fw-semibold">{{ $q['correct'] }}</td><td class="text-danger fw-semibold">{{ $q['incorrect'] }}</td></tr>
        @endforeach
        </tbody>
    </table></div>
</x-card>
@endsection
@if ($quiz->isActive())
@push('scripts')<script>setTimeout(function () { location.reload(); }, 5000);</script>@endpush
@endif

