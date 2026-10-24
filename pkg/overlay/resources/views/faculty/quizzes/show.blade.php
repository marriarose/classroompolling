@extends('layouts.app')
@section('title', $quiz->title)
@section('content')
<div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-4">
    <div>
        <a href="{{ route('faculty.classrooms.show', $quiz->classroom) }}" class="small">&larr; {{ $quiz->classroom->name }}</a>
        <h2 class="mb-1">{{ $quiz->title }}
            <span class="badge fs-6 {{ ['draft' => 'bg-secondary', 'active' => 'bg-success', 'closed' => 'bg-dark'][$quiz->status] }}">{{ $quiz->status }}</span></h2>
        @if ($quiz->description)<p class="text-muted mb-0">{{ $quiz->description }}</p>@endif
    </div>
    <div class="d-flex gap-2">
        @if ($quiz->isDraft())
            <form method="POST" action="{{ route('faculty.quizzes.start', $quiz) }}">@csrf
                <button class="btn btn-success btn-lg">▶ Start Quiz</button></form>
        @elseif ($quiz->isActive())
            <form method="POST" action="{{ route('faculty.quizzes.close', $quiz) }}"
                  onsubmit="return confirm('Close this round? Students will no longer be able to submit answers.');">@csrf
                <button class="btn btn-danger btn-lg">■ Close Round</button></form>
        @else
            <a class="btn btn-primary btn-lg" href="{{ route('faculty.quizzes.results', $quiz) }}">View results</a>
        @endif
    </div>
</div>

@if ($quiz->isActive())
    <x-card>
        <div class="fw-semibold mb-2">Live participation:
            <span class="badge bg-primary" id="participating">0</span> student(s) have answered at least one question</div>
        <div class="table-responsive"><table class="table table-sm mb-0">
            <thead><tr><th>Student</th><th>Progress</th></tr></thead><tbody id="participation-body"></tbody></table></div>
    </x-card>
@endif

<div class="row">
    <div class="col-lg-7">
        <x-card :title="'Questions ('.$quiz->questions->count().')'">
            @forelse ($quiz->questions as $q)
                <div class="border rounded p-3 mb-3">
                    <div class="d-flex justify-content-between">
                        <strong>Q{{ $loop->iteration }}. {{ $q->question_text }}</strong>
                        <span class="badge bg-light text-dark">{{ $q->points }} pt</span>
                    </div>
                    <ul class="mb-2 mt-2 ps-3">
                        @foreach ($q->options as $o)
                            <li @class(['text-success fw-semibold' => $o->is_correct])>{{ $o->option_text }} @if ($o->is_correct) ✓ @endif</li>
                        @endforeach
                    </ul>
                    @if ($quiz->isDraft())
                        <form method="POST" action="{{ route('faculty.questions.destroy', $q) }}" onsubmit="return confirm('Remove this question?');">@csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Remove</button></form>
                    @endif
                </div>
            @empty
                <p class="text-muted mb-0">No questions yet.</p>
            @endforelse
        </x-card>
    </div>

    @if ($quiz->isDraft())
    <div class="col-lg-5">
        <x-card title="Add question">
            <form method="POST" action="{{ route('faculty.questions.store', $quiz) }}">@csrf
                <div class="mb-3"><label class="form-label">Question</label>
                    <textarea name="question_text" rows="2" class="form-control" required>{{ old('question_text') }}</textarea></div>
                <div class="mb-3"><label class="form-label">Points</label>
                    <input type="number" name="points" min="1" max="100" value="{{ old('points', 1) }}" class="form-control" required></div>
                @foreach (['A', 'B', 'C', 'D'] as $L)
                    <div class="mb-2"><label class="form-label">Option {{ $L }} {{ in_array($L, ['A', 'B']) ? '' : '(optional)' }}</label>
                        <input name="options[{{ $L }}]" value="{{ old("options.$L") }}" class="form-control" {{ in_array($L, ['A', 'B']) ? 'required' : '' }}></div>
                @endforeach
                <div class="mb-3"><label class="form-label">Correct answer</label>
                    <select name="correct_option" class="form-select" required>
                        @foreach (['A', 'B', 'C', 'D'] as $L)<option value="{{ $L }}" @selected(old('correct_option') === $L)>{{ $L }}</option>@endforeach
                    </select></div>
                <button class="btn btn-primary">Save Question</button>
            </form>
        </x-card>
    </div>
    @endif
</div>
@endsection
@if ($quiz->isActive())
@push('scripts')
<script>
startPolling(@json(route('faculty.quizzes.participation', $quiz)), function (d) {
    if (d.status !== 'active') { location.reload(); return; }
    document.getElementById('participating').textContent = d.participating;
    var tb = document.getElementById('participation-body'); tb.innerHTML = '';
    d.students.forEach(function (s) {
        var tr = document.createElement('tr'), a = document.createElement('td'), b = document.createElement('td');
        a.textContent = s.name; b.textContent = s.answered + ' / ' + d.total_questions;
        tr.appendChild(a); tr.appendChild(b); tb.appendChild(tr);
    });
}, 3000);
</script>
@endpush
@endif
