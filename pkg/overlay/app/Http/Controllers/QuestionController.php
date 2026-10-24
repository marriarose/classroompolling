<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreQuestionRequest;
use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Support\Facades\DB;

class QuestionController extends Controller
{
    public function store(StoreQuestionRequest $request, Quiz $quiz)
    {
        $this->authorize('manage', $quiz);

        if (! $quiz->isDraft()) {
            return back()->with('error', 'Questions can only be added while the quiz is a draft.');
        }

        DB::transaction(function () use ($request, $quiz) {
            $question = $quiz->questions()->create([
                'question_text' => $request->validated('question_text'),
                'points' => $request->validated('points'),
                'order' => ((int) $quiz->questions()->max('order')) + 1,
            ]);

            foreach (['A', 'B', 'C', 'D'] as $letter) {
                $text = $request->input("options.$letter");
                if (filled($text)) {
                    $question->options()->create([
                        'option_text' => $text,
                        'is_correct' => $letter === $request->validated('correct_option'),
                    ]);
                }
            }
        });

        return back()->with('success', 'Question saved.');
    }

    public function destroy(Question $question)
    {
        $quiz = $question->quiz;
        $this->authorize('manage', $quiz);

        if (! $quiz->isDraft()) {
            return back()->with('error', 'Questions can only be removed while the quiz is a draft.');
        }

        $question->delete();

        return back()->with('success', 'Question removed.');
    }
}
