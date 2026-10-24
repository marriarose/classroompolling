@props(['question', 'number', 'total', 'action'])
<form method="POST" action="{{ $action }}" id="answer-form">
    @csrf
    <input type="hidden" name="question_id" value="{{ $question->id }}">
    <div class="progress mb-3" style="height:8px"><div class="progress-bar" style="width: {{ $total ? round((($number - 1) / $total) * 100) : 0 }}%"></div></div>
    <p class="text-muted mb-1">Question {{ $number }} of {{ $total }} · {{ $question->points }} {{ \Illuminate\Support\Str::plural('point', $question->points) }}</p>
    <h4 class="mb-4">{{ $question->question_text }}</h4>
    @foreach ($question->options as $option)
        <input type="radio" class="btn-check" name="option_id" id="opt{{ $option->id }}" value="{{ $option->id }}" required>
        <label class="btn btn-outline-primary btn-lg w-100 text-start mb-2 option-label" for="opt{{ $option->id }}">{{ $option->option_text }}</label>
    @endforeach
    <button type="submit" class="btn btn-primary btn-lg w-100 mt-3" id="submit-btn">Submit Answer</button>
</form>
<script>
    document.getElementById('answer-form').addEventListener('submit', function () {
        var b = document.getElementById('submit-btn'); b.disabled = true; b.textContent = 'Submitting...';
    });
</script>
