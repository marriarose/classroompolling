<script>
(function () {
    var initial = @json($state);
    startPolling(@json(route('student.quizzes.status', $quiz)), function (d) {
        if (d.quiz_closed) { location.href = d.result_url; }
        else if (d.state !== initial) { location.reload(); }
    }, 3000);
})();
</script>
