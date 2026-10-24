<nav class="navbar navbar-dark bg-primary px-3">
    <a class="navbar-brand fw-bold" href="{{ route('dashboard') }}">📊 ClassQuiz</a>
    @auth
        <div class="d-flex align-items-center gap-3 text-white">
            <span class="d-none d-sm-inline">{{ auth()->user()->name }} <span class="badge bg-light text-primary text-capitalize">{{ auth()->user()->role }}</span></span>
            <form method="POST" action="{{ route('logout') }}">@csrf
                <button class="btn btn-sm btn-outline-light">Logout</button>
            </form>
        </div>
    @endauth
</nav>
