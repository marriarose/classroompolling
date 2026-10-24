<ul class="nav {{ ($horizontal ?? false) ? 'nav-pills' : 'flex-column' }} gap-1">
    <li class="nav-item"><a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="nav-item"><a class="nav-link {{ request()->routeIs('faculty.classrooms.*', 'faculty.quizzes.show') ? 'active' : '' }}" href="{{ route('faculty.classrooms.index') }}">Classrooms</a></li>
    <li class="nav-item"><a class="nav-link {{ request()->routeIs('faculty.results.*', 'faculty.quizzes.results') ? 'active' : '' }}" href="{{ route('faculty.results.index') }}">Results</a></li>
</ul>
