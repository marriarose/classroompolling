<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreQuizRequest;
use App\Models\Classroom;
use App\Models\Quiz;
use App\Models\Response;
use Illuminate\Support\Facades\DB;

class QuizController extends Controller
{
    public function store(StoreQuizRequest $request, Classroom $classroom)
    {
        $this->authorize('manage', $classroom);

        $quiz = $classroom->quizzes()->create($request->validated() + ['status' => 'draft']);

        return redirect()->route('faculty.quizzes.show', $quiz)
            ->with('success', 'Quiz created. Now add your questions.');
    }

    public function show(Quiz $quiz)
    {
        $this->authorize('manage', $quiz);
        $quiz->load(['classroom', 'questions.options']);

        return view('faculty.quizzes.show', compact('quiz'));
    }

    public function start(Quiz $quiz)
    {
        $this->authorize('manage', $quiz);

        $error = DB::transaction(function () use ($quiz) {
            $locked = Quiz::whereKey($quiz->id)->lockForUpdate()->firstOrFail();

            if (! $locked->isDraft()) {
                return 'Only a draft quiz can be started.';
            }
            if (! $locked->classroom->isActive()) {
                return 'The classroom is not active.';
            }
            if (! $locked->questions()->exists()) {
                return 'Add at least one question before starting.';
            }
            if ($locked->classroom->quizzes()->where('status', 'active')->exists()) {
                return 'Another quiz is already running in this classroom. Close it first.';
            }

            $locked->update(['status' => 'active', 'started_at' => now()]);

            return null;
        });

        return $error
            ? back()->with('error', $error)
            : back()->with('success', 'Quiz started. Students can answer now.');
    }

    public function close(Quiz $quiz)
    {
        $this->authorize('manage', $quiz);

        $closed = DB::transaction(function () use ($quiz) {
            $locked = Quiz::whereKey($quiz->id)->lockForUpdate()->firstOrFail();
            if (! $locked->isActive()) {
                return false;
            }
            $locked->update(['status' => 'closed', 'ended_at' => now()]);

            return true;
        });

        if (! $closed) {
            return back()->with('error', 'Only an active quiz can be closed.');
        }

        // Scores are stored per answer at submission time; the scoreboard is computed from them.
        return redirect()->route('faculty.quizzes.results', $quiz)
            ->with('success', 'Round closed. Final results below.');
    }

    /** JSON: live participation for the faculty monitor. */
    public function participation(Quiz $quiz)
    {
        $this->authorize('manage', $quiz);

        $counts = Response::where('quiz_id', $quiz->id)
            ->selectRaw('student_id, COUNT(*) AS answered')
            ->groupBy('student_id')->pluck('answered', 'student_id');

        $students = $quiz->classroom->students()->get()->map(fn ($s) => [
            'name' => $s->pivot->display_name ?: $s->name,
            'answered' => (int) ($counts[$s->id] ?? 0),
        ])->values();

        return response()->json([
            'status' => $quiz->fresh()->status,
            'total_questions' => $quiz->questions()->count(),
            'students' => $students,
            'participating' => $students->where('answered', '>', 0)->count(),
        ]);
    }
}
