<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Classroom Quiz')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
<x-navbar />
@auth
    @if (auth()->user()->isFaculty())
        <div class="container-fluid">
            <div class="row">
                <aside class="col-md-2 sidebar d-none d-md-block p-3">
                    @include('layouts.partials.faculty-links')
                </aside>
                <main class="col-12 col-md-10 p-3 p-md-4">
                    <div class="d-md-none mb-3">@include('layouts.partials.faculty-links', ['horizontal' => true])</div>
                    <x-alert />
                    @yield('content')
                </main>
            </div>
        </div>
    @else
        <main class="container py-4" style="max-width: 760px;">
            <x-alert />
            @yield('content')
        </main>
    @endif
@else
    <main class="container py-5" style="max-width: 520px;">
        <x-alert />
        @yield('content')
    </main>
@endauth
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/poll.js') }}"></script>
@stack('scripts')
</body>
</html>
