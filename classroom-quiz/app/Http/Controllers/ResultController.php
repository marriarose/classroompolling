<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Services\QuizScoringService;
use Illuminate\Http\Request;

class ResultController extends Controller
{
    public function index(Request $request)
    {
        $quizzes = Quiz::whereHas('classroom', fn ($q) => $q->where('faculty_id', $request->user()->id))
            ->where('status', 'closed')->with('classroom')->latest('ended_at')->get();

        return view('faculty.results.index', compact('quizzes'));
    }

    public function show(Quiz $quiz, QuizScoringService $scoring)
    {
        $this->authorize('manage', $quiz);
        $quiz->load('classroom');

        return view('faculty.results.show', ['quiz' => $quiz] + $scoring->summary($quiz));
    }

    public function json(Quiz $quiz, QuizScoringService $scoring)
    {
        $this->authorize('manage', $quiz);
        $quiz->load('classroom');

        return response()->json(['status' => $quiz->status] + $scoring->summary($quiz));
    }
}
