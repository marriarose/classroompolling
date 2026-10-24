<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\Quiz;
use App\Models\Response;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $faculty = User::create(['name' => 'Dr. Priya Nair', 'email' => 'faculty@example.com', 'password' => 'password', 'role' => 'faculty']);

        $students = collect([
            ['Demo Student', 'student@example.com'],
            ['Anu', 'anu@example.com'], ['Maria', 'maria@example.com'],
            ['Rahul', 'rahul@example.com'], ['Neha', 'neha@example.com'],
        ])->map(fn ($s) => User::create(['name' => $s[0], 'email' => $s[1], 'password' => 'password', 'role' => 'student']));

        $ai = Classroom::create(['faculty_id' => $faculty->id, 'name' => 'AI Fundamentals', 'join_code' => 'DEMK42', 'status' => 'active']);
        $ml = Classroom::create(['faculty_id' => $faculty->id, 'name' => 'Machine Learning', 'join_code' => 'MLRN26', 'status' => 'active']);

        foreach ($students as $s) {
            $ai->students()->attach($s->id, ['display_name' => $s->name, 'joined_at' => now()]);
        }
        foreach ($students->take(3) as $s) {
            $ml->students()->attach($s->id, ['display_name' => $s->name, 'joined_at' => now()]);
        }

        // Quiz 1: closed, with sample responses.
        $closed = $this->makeQuiz($ai, 'AI Basics Quiz', 'closed', [
            ['What does AI stand for?', 1, ['Artificial Intelligence', 'Automated Interface', 'Applied Inference', 'Analog Integration'], 0],
            ['What is supervised learning?', 1, ['Learning without data', 'Learning from labelled data', 'Learning by reward only', 'Memorising rules'], 1],
            ['Which algorithm is commonly used for classification?', 2, ['Linear Regression', 'Logistic Regression', 'K-Means', 'PCA'], 1],
            ['Which of these is an unsupervised method?', 1, ['K-Means', 'Logistic Regression', 'Decision Tree', 'SVM'], 0],
            ['What does CNN stand for?', 1, ['Central Neural Net', 'Convolutional Neural Network', 'Complex Node Network', 'Cyclic Neuron Network'], 1],
        ]);
        $closed->update(['started_at' => now()->subHour(), 'ended_at' => now()->subMinutes(30)]);

        foreach ($students as $i => $student) {
            foreach ($closed->questions()->with('options')->get() as $q => $question) {
                $wantCorrect = (($q + $i) % 3) !== 0;
                $option = $question->options->first(fn ($o) => $o->is_correct === $wantCorrect);
                Response::create([
                    'quiz_id' => $closed->id, 'question_id' => $question->id, 'student_id' => $student->id,
                    'option_id' => $option->id, 'is_correct' => $option->is_correct,
                    'points_awarded' => $option->is_correct ? $question->points : 0,
                    'answered_at' => now()->subMinutes(55 - $q - $i),
                ]);
            }
        }

        // Quiz 2: draft, ready for faculty to start.
        $this->makeQuiz($ml, 'ML Warm-up Poll', 'draft', [
            ['Which metric suits imbalanced classification best?', 1, ['Accuracy', 'F1-score', 'MSE', 'R²'], 1],
            ['What is overfitting?', 1, ['Model too simple', 'Model memorises training data', 'Too little training time', 'Missing labels'], 1],
            ['Which technique helps reduce overfitting?', 1, ['Regularisation', 'Adding noise to labels', 'Fewer samples', 'Larger learning rate'], 0],
        ]);
    }

    private function makeQuiz(Classroom $classroom, string $title, string $status, array $questions): Quiz
    {
        $quiz = $classroom->quizzes()->create(['title' => $title, 'description' => 'Demo quiz', 'status' => $status]);
        foreach ($questions as $i => [$text, $points, $options, $correct]) {
            $question = $quiz->questions()->create(['question_text' => $text, 'points' => $points, 'order' => $i + 1]);
            foreach ($options as $j => $optionText) {
                $question->options()->create(['option_text' => $optionText, 'is_correct' => $j === $correct]);
            }
        }

        return $quiz;
    }
}
