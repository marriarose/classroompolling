@extends('layouts.app')
@section('title', 'Classrooms')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Classrooms</h2>
    <a href="{{ route('faculty.classrooms.create') }}" class="btn btn-primary">+ Create Classroom</a>
</div>
<x-card>
    <div class="table-responsive">
    <table class="table align-middle mb-0">
        <thead><tr><th>Name</th><th>Join code</th><th>Students</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @forelse ($classrooms as $c)
            <tr>
                <td>{{ $c->name }}</td>
                <td class="join-code">{{ $c->join_code }}</td>
                <td>{{ $c->students_count }}</td>
                <td><span class="badge {{ $c->isActive() ? 'bg-success' : 'bg-secondary' }}">{{ $c->status }}</span></td>
                <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="{{ route('faculty.classrooms.show', $c) }}">Open</a></td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-muted">No classrooms yet.</td></tr>
        @endforelse
        </tbody>
    </table>
    </div>
</x-card>
@endsection
