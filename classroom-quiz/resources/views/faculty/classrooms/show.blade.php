@extends('layouts.app')
@section('title', $classroom->name)
@section('content')
<div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-4">
    <div>
        <h2 class="mb-1">{{ $classroom->name }}
            <span class="badge fs-6 {{ $classroom->isActive() ? 'bg-success' : 'bg-secondary' }}">{{ $classroom->status }}</span></h2>
        <div class="text-muted">Join code <span class="join-code fs-3 text-primary ms-2">{{ $classroom->join_code }}</span></div>
        <div class="small text-muted">Students go to <code>{{ url('/join') }}</code></div>
    </div>
    <form method="POST" action="{{ route('faculty.classrooms.toggle', $classroom) }}">@csrf @method('PATCH')
        <button class="btn btn-outline-secondary">{{ $classroom->isActive() ? 'Close room' : 'Re-open room' }}</button>
    </form>
</div>

<div class="row">
    <div class="col-lg-7">
        <x-card title="Quizzes" id="quizzes">
            <div class="table-responsive"><table class="table align-middle mb-0">
                <thead><tr><th>Title</th><th>Questions</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                @forelse ($classroom->quizzes as $quiz)
                    <tr>
                        <td>{{ $quiz->title }}</td>
                        <td>{{ $quiz->questions_count }}</td>
                        <td><span class="badge {{ ['draft' => 'bg-secondary', 'active' => 'bg-success', 'closed' => 'bg-dark'][$quiz->status] }}">{{ $quiz->status }}</span></td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('faculty.quizzes.show', $quiz) }}">{{ $quiz->isDraft() ? 'Edit / Start' : 'Manage' }}</a>
                            @if (! $quiz->isDraft())<a class="btn btn-sm btn-outline-secondary" href="{{ route('faculty.quizzes.results', $quiz) }}">Results</a>@endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-muted">No quizzes yet.</td></tr>
                @endforelse
                </tbody>
            </table></div>
        </x-card>

        <x-card title="Create quiz / poll" id="new-quiz">
            <form method="POST" action="{{ route('faculty.quizzes.store', $classroom) }}">@csrf
                <div class="mb-3"><label class="form-label">Quiz title</label><input name="title" value="{{ old('title') }}" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Description (optional)</label><textarea name="description" rows="2" class="form-control">{{ old('description') }}</textarea></div>
                <button class="btn btn-primary">Create quiz</button>
            </form>
        </x-card>
    </div>

    <div class="col-lg-5">
        <x-card id="students">
            <div class="fw-semibold mb-2">Students joined: <span id="student-count" class="badge bg-primary">{{ $classroom->students->count() }}</span></div>
            <ul class="list-group list-group-flush" id="student-list">
                @forelse ($classroom->students as $s)
                    <li class="list-group-item px-0">{{ $s->pivot->display_name ?: $s->name }}</li>
                @empty
                    <li class="list-group-item px-0 text-muted">Waiting for students to join...</li>
                @endforelse
            </ul>
        </x-card>
    </div>
</div>
@endsection
@push('scripts')
<script>
startPolling(@json(route('faculty.classrooms.students', $classroom)), function (d) {
    document.getElementById('student-count').textContent = d.count;
    var ul = document.getElementById('student-list'); ul.innerHTML = '';
    if (!d.students.length) { var e = document.createElement('li'); e.className = 'list-group-item px-0 text-muted'; e.textContent = 'Waiting for students to join...'; ul.appendChild(e); }
    d.students.forEach(function (n) { var li = document.createElement('li'); li.className = 'list-group-item px-0'; li.textContent = n; ul.appendChild(li); });
}, 4000);
</script>
@endpush
