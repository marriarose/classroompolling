<?php

namespace App\Http\Controllers;

use App\Http\Requests\JoinClassroomRequest;
use App\Models\Classroom;
use App\Models\Quiz;
use App\Models\Response;
use App\Services\QuizScoringService;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function joinForm()
    {
        return view('student.join');
    }

    public function join(JoinClassroomRequest $request)
    {
        $code = strtoupper(trim($request->validated('join_code')));
        $classroom = Classroom::where('join_code', $code)->where('status', 'active')->first();

        if (! $classroom) {
            return back()->withInput()->withErrors([
                'join_code' => 'The classroom code is invalid or the room is no longer active.',
            ]);
        }

        // syncWithoutDetaching => joining twice never duplicates the row (unique index backs this up)
        $classroom->students()->syncWithoutDetaching([
            $request->user()->id => [
                'display_name' => $request->validated('display_name'),
                'joined_at' => now(),
            ],
        ]);

        return redirect()->route('student.classrooms.show', $classroom)
            ->with('success', "You joined {$classroom->name}.");
    }

    public function classroom(Classroom $classroom)
    {
        $this->authorize('participate', $classroom);

        $classroom->load('faculty');
        $activeQuiz = $classroom->quizzes()->where('status', 'active')->first();
        $closedQuizzes = $classroom->quizzes()->where('status', 'closed')->latest('ended_at')->get();
        $participants = $classroom->students()->count();

        return view('student.waiting', compact('classroom', 'activeQuiz', 'closedQuizzes', 'participants'));
    }

    public function classroomStatus(Classroom $classroom)
    {
        $this->authorize('participate', $classroom);
        $active = $classroom->quizzes()->where('status', 'active')->first();

        return response()->json([
            'participants' => $classroom->students()->count(),
            'active_quiz_id' => $active?->id,
            'active_quiz_url' => $active ? route('student.quizzes.show', $active) : null,
        ]);
    }

    public function quiz(Request $request, Quiz $quiz)
    {
        $this->authorize('participate', $quiz);
        $quiz->load('classroom');

        if ($quiz->isClosed()) {
            $request->session()->reflash();

            return redirect()->route('student.quizzes.result', $quiz);
        }

        $total = $quiz->questions()->count();
        $answeredIds = Response::where('quiz_id', $quiz->id)
            ->where('student_id', $request->user()->id)->pluck('question_id')->all();
        $state = $quiz->status.':'.count($answeredIds);

        if ($quiz->isDraft()) {
            return view('student.quiz-waiting', ['quiz' => $quiz, 'state' => $state, 'message' => 'Waiting for the quiz to start...']);
        }

        $next = $quiz->questions()->get()->first(fn ($q) => ! in_array($q->id, $answeredIds, true));

        if (! $next) {
            return view('student.quiz-waiting', [
                'quiz' => $quiz, 'state' => $state,
                'message' => 'All your answers are in. Waiting for the faculty to close the round...',
            ]);
        }

        // Only id + text: is_correct is never selected for students.
        $next->setRelation('options', $next->options()->select('id', 'question_id', 'option_text')->get());

        return view('student.quiz', [
            'quiz' => $quiz, 'question' => $next, 'number' => count($answeredIds) + 1,
            'total' => $total, 'state' => $state,
        ]);
    }

    public function status(Request $request, Quiz $quiz)
    {
        $this->authorize('participate', $quiz);

        $answered = Response::where('quiz_id', $quiz->id)->where('student_id', $request->user()->id)->count();
        $total = $quiz->questions()->count();

        return response()->json([
            'status' => $quiz->status,
            'quiz_closed' => $quiz->isClosed(),
            'current_question' => min($answered + 1, max($total, 1)),
            'answered' => $answered,
            'total_questions' => $total,
            'state' => $quiz->status.':'.$answered,
            'result_url' => route('student.quizzes.result', $quiz),
        ]);
    }

    public function result(Request $request, Quiz $quiz, QuizScoringService $scoring)
    {
        $this->authorize('participate', $quiz);

        if (! $quiz->isClosed()) {
            return redirect()->route('student.quizzes.show', $quiz);
        }

        $quiz->load('classroom');
        $row = $scoring->scoreboard($quiz)->firstWhere('student_id', $request->user()->id);

        return view('student.result', compact('quiz', 'row'));
    }
}
