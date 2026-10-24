<?php

namespace App\Services;

use App\Exceptions\QuizException;
use App\Models\Quiz;
use App\Models\Response;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * All answer validation and scoring happens here, on the server.
 * The browser only ever supplies question_id and option_id.
 */
class QuizScoringService
{
    public function submitAnswer(Quiz $quiz, User $student, int $questionId, int $optionId): Response
    {
        return DB::transaction(function () use ($quiz, $student, $questionId, $optionId) {
            // Lock the quiz row so "close" and "answer" cannot interleave.
            $quiz = Quiz::whereKey($quiz->id)->lockForUpdate()->firstOrFail();

            if ($quiz->isDraft()) {
                throw new QuizException('This quiz has not started yet.');
            }
            if ($quiz->isClosed()) {
                throw new QuizException('This round has ended. Your response was not accepted.');
            }

            $question = $quiz->questions()->whereKey($questionId)->first();
            if (! $question) {
                throw new QuizException('This question does not belong to the active quiz.');
            }

            $option = $question->options()->whereKey($optionId)->first();
            if (! $option) {
                throw new QuizException('The selected option is not valid for this question.');
            }

            $alreadyAnswered = Response::where('student_id', $student->id)
                ->where('question_id', $question->id)->exists();
            if ($alreadyAnswered) {
                throw new QuizException('You have already answered this question.');
            }

            try {
                return Response::create([
                    'quiz_id' => $quiz->id,
                    'question_id' => $question->id,
                    'student_id' => $student->id,
                    'option_id' => $option->id,
                    'is_correct' => $option->is_correct,                       // from DB, never from the browser
                    'points_awarded' => $option->is_correct ? $question->points : 0,
                    'answered_at' => now(),
                ]);
            } catch (UniqueConstraintViolationException) {
                throw new QuizException('You have already answered this question.');
            }
        });
    }

    /**
     * Ranked rows for every student in the quiz's classroom.
     * Order: points DESC, then earliest last-answer time (tie-break).
     */
    public function scoreboard(Quiz $quiz): Collection
    {
        $totalQuestions = $quiz->questions()->count();
        $totalPoints = (int) $quiz->questions()->sum('points');

        $totals = Response::where('quiz_id', $quiz->id)
            ->selectRaw('student_id, SUM(points_awarded) AS total_points, SUM(CASE WHEN is_correct THEN 1 ELSE 0 END) AS correct_count, MAX(answered_at) AS last_answered_at')
            ->groupBy('student_id')
            ->get()
            ->keyBy('student_id');

        $rows = $quiz->classroom->students()->get()->map(function ($student) use ($totals, $totalQuestions, $totalPoints) {
            $t = $totals->get($student->id);
            $points = (int) ($t->total_points ?? 0);

            return [
                'student_id' => $student->id,
                'name' => $student->pivot->display_name ?: $student->name,
                'points' => $points,
                'total_points' => $totalPoints,
                'correct' => (int) ($t->correct_count ?? 0),
                'total_questions' => $totalQuestions,
                'percentage' => $totalPoints > 0 ? round($points / $totalPoints * 100, 2) : 0,
                'answered' => $t !== null,
                'last_ts' => $t && $t->last_answered_at ? strtotime($t->last_answered_at) : PHP_INT_MAX,
            ];
        });

        return $rows
            ->sort(fn ($a, $b) => [$b['points'], $a['last_ts']] <=> [$a['points'], $b['last_ts']])
            ->values()
            ->map(fn ($row, $i) => $row + ['rank' => $i + 1]);
    }

    /** Scoreboard + aggregate stats + per-question stats. */
    public function summary(Quiz $quiz): array
    {
        $rows = $this->scoreboard($quiz);
        $count = $rows->count();
        $totalPoints = (int) $quiz->questions()->sum('points');

        $stats = [
            'total_students' => $count,
            'average_score' => $count ? round($rows->avg('points'), 2) : 0,
            'highest_score' => $count ? $rows->max('points') : 0,
            'lowest_score' => $count ? $rows->min('points') : 0,
            'total_points' => $totalPoints,
            'participation_rate' => $count ? round($rows->where('answered', true)->count() / $count * 100, 2) : 0,
        ];

        $questions = $quiz->questions()
            ->withCount([
                'responses as correct_count' => fn ($q) => $q->where('is_correct', true),
                'responses as incorrect_count' => fn ($q) => $q->where('is_correct', false),
            ])->get()
            ->map(fn ($q) => [
                'question' => $q->question_text,
                'correct' => $q->correct_count,
                'incorrect' => $q->incorrect_count,
            ]);

        return ['rows' => $rows, 'stats' => $stats, 'questions' => $questions];
    }
}
