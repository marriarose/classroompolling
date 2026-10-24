<x-card title="Join Classroom">
    <form method="POST" action="{{ route('student.join.store') }}">@csrf
        <div class="mb-3"><label class="form-label">Classroom code</label>
            <input name="join_code" value="{{ old('join_code') }}" maxlength="6" class="form-control form-control-lg join-input" placeholder="X7K2P9" autocomplete="off" required></div>
        <div class="mb-3"><label class="form-label">Your name</label>
            <input name="display_name" value="{{ old('display_name', auth()->user()->name) }}" maxlength="60" class="form-control form-control-lg" required></div>
        <button class="btn btn-primary btn-lg w-100">Join Classroom</button>
    </form>
</x-card>
