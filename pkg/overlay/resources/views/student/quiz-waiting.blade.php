@extends('layouts.app')
@section('title', $quiz->title)
@section('content')
<x-card class="text-center">
    <h4>{{ $quiz->title }}</h4>
    <div class="spinner-border text-primary my-3" role="status"></div>
    <p class="fs-5 mb-0">{{ $message }}</p>
</x-card>
@endsection
@push('scripts')@include('student.partials.status-poll')@endpush
