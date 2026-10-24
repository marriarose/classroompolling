<?php

namespace App\Http\Controllers;

use App\Exceptions\QuizException;
use App\Http\Requests\AnswerRequest;
use App\Models\Quiz;
use App\Services\QuizScoringService;

class ResponseController extends Controller
{
    public function store(AnswerRequest $request, Quiz $quiz, QuizScoringService $scoring)
    {
        $this->authorize('participate', $quiz);

        try {
            $scoring->submitAnswer(
                $quiz,
                $request->user(),
                (int) $request->validated('question_id'),
                (int) $request->validated('option_id'),
            );
        } catch (QuizException $e) {
            return redirect()->route('student.quizzes.show', $quiz)->with('error', $e->getMessage());
        }

        return redirect()->route('student.quizzes.show', $quiz)
            ->with('success', 'Answer submitted successfully.');
    }
}
