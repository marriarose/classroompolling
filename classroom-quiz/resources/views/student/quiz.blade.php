@extends('layouts.app')
@section('title', $quiz->title)
@section('content')
<p class="text-muted mb-2">{{ $quiz->classroom->name }} · {{ $quiz->title }}</p>
<x-card>
    <x-question :question="$question" :number="$number" :total="$total" :action="route('student.quizzes.answer', $quiz)" />
</x-card>
@endsection
@push('scripts')@include('student.partials.status-poll')@endpush
