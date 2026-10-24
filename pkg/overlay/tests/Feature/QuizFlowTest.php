<?php

namespace Tests\Feature;

use App\Models\Classroom;
use App\Models\Quiz;
use App\Models\Response;
use App\Models\User;
use App\Services\QuizScoringService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuizFlowTest extends TestCase
{
    use RefreshDatabase;

    private function room(?User $faculty = null): array
    {
        $faculty ??= User::factory()->faculty()->create();
        $classroom = Classroom::create(['faculty_id' => $faculty->id, 'name' => 'AI', 'join_code' => Classroom::generateJoinCode(), 'status' => 'active']);
        $student = User::factory()->student()->create();
        $classroom->students()->attach($student->id, ['display_name' => 'Stu', 'joined_at' => now()]);

        return [$faculty, $classroom, $student];
    }

    /** Active quiz with two 1-point... Q1 = 2 pts, Q2 = 1 pt. Returns [quiz, [q1,q2]]. */
    private function quiz(Classroom $classroom, string $status = 'active'): array
    {
        $quiz = $classroom->quizzes()->create(['title' => 'Q', 'status' => $status]);
        $qs = [];
        foreach ([2, 1] as $i => $pts) {
            $q = $quiz->questions()->create(['question_text' => "Q$i?", 'points' => $pts, 'order' => $i + 1]);
            $q->options()->create(['option_text' => 'right', 'is_correct' => true]);
            $q->options()->create(['option_text' => 'wrong', 'is_correct' => false]);
            $qs[] = $q;
        }

        return [$quiz, $qs];
    }

    private function opt($question, bool $correct)
    {
        return $question->options()->where('is_correct', $correct)->first();
    }

    // ---- Classroom ----
    public function test_faculty_can_create_classroom_with_valid_join_code(): void
    {
        $faculty = User::factory()->faculty()->create();
        $this->actingAs($faculty)->post('/faculty/classrooms', ['name' => 'Physics'])->assertRedirect();
        $c = Classroom::firstOrFail();
        $this->assertMatchesRegularExpression('/^[A-HJ-NP-Z2-9]{6}$/', $c->join_code);
    }

    public function test_join_codes_are_unique(): void
    {
        $faculty = User::factory()->faculty()->create();
        $codes = [];
        for ($i = 0; $i < 30; $i++) {
            $code = Classroom::generateJoinCode();
            Classroom::create(['faculty_id' => $faculty->id, 'name' => "C$i", 'join_code' => $code, 'status' => 'active']);
            $codes[] = $code;
        }
        $this->assertCount(30, array_unique($codes));
    }

    // ---- Student join ----
    public function test_student_joins_with_lowercase_code(): void
    {
        $faculty = User::factory()->faculty()->create();
        $c = Classroom::create(['faculty_id' => $faculty->id, 'name' => 'X', 'join_code' => 'ABCD23', 'status' => 'active']);
        $student = User::factory()->student()->create();

        $this->actingAs($student)->post('/join', ['join_code' => 'abcd23', 'display_name' => 'Sam'])
            ->assertRedirect(route('student.classrooms.show', $c));
        $this->assertDatabaseHas('classroom_students', ['classroom_id' => $c->id, 'student_id' => $student->id, 'display_name' => 'Sam']);
    }

    public function test_invalid_join_code_is_rejected(): void
    {
        $student = User::factory()->student()->create();
        $this->actingAs($student)->post('/join', ['join_code' => 'ZZZZZ9', 'display_name' => 'Sam'])
            ->assertSessionHasErrors('join_code');
    }

    // ---- Quiz lifecycle ----
    public function test_faculty_can_create_start_and_close_quiz(): void
    {
        [$faculty, $classroom] = $this->room();
        $this->actingAs($faculty)->post("/faculty/classrooms/{$classroom->id}/quiz", ['title' => 'Midterm'])->assertRedirect();
        $quiz = Quiz::firstOrFail();
        $this->assertSame('draft', $quiz->status);

        $this->post("/faculty/quizzes/{$quiz->id}/questions", [
            'question_text' => 'What?', 'points' => 2,
            'options' => ['A' => 'a', 'B' => 'b', 'C' => '', 'D' => ''], 'correct_option' => 'B',
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('options', ['option_text' => 'b', 'is_correct' => true]);

        $this->post("/faculty/quizzes/{$quiz->id}/start");
        $this->assertSame('active', $quiz->fresh()->status);

        $this->post("/faculty/quizzes/{$quiz->id}/close");
        $this->assertSame('closed', $quiz->fresh()->status);
        $this->assertNotNull($quiz->fresh()->ended_at);
    }

    public function test_correct_option_must_be_filled_in(): void
    {
        [$faculty, $classroom] = $this->room();
        [$quiz] = $this->quiz($classroom, 'draft');
        $this->actingAs($faculty)->post("/faculty/quizzes/{$quiz->id}/questions", [
            'question_text' => 'x', 'points' => 1, 'options' => ['A' => 'a', 'B' => 'b'], 'correct_option' => 'D',
        ])->assertSessionHasErrors('correct_option');
    }

    // ---- Answers / scoring ----
    public function test_correct_answer_earns_points_and_wrong_earns_zero(): void
    {
        [, $classroom, $student] = $this->room();
        [$quiz, [$q1, $q2]] = $this->quiz($classroom);

        $this->actingAs($student)->post("/student/quizzes/{$quiz->id}/answer", ['question_id' => $q1->id, 'option_id' => $this->opt($q1, true)->id]);
        $this->post("/student/quizzes/{$quiz->id}/answer", ['question_id' => $q2->id, 'option_id' => $this->opt($q2, false)->id]);

        $this->assertDatabaseHas('responses', ['question_id' => $q1->id, 'is_correct' => true, 'points_awarded' => 2]);
        $this->assertDatabaseHas('responses', ['question_id' => $q2->id, 'is_correct' => false, 'points_awarded' => 0]);
    }

    public function test_browser_supplied_score_fields_are_ignored(): void
    {
        [, $classroom, $student] = $this->room();
        [$quiz, [$q1]] = $this->quiz($classroom);
        $this->actingAs($student)->post("/student/quizzes/{$quiz->id}/answer", [
            'question_id' => $q1->id, 'option_id' => $this->opt($q1, false)->id,
            'is_correct' => 1, 'points_awarded' => 999,
        ]);
        $this->assertDatabaseHas('responses', ['question_id' => $q1->id, 'is_correct' => false, 'points_awarded' => 0]);
    }

    public function test_student_cannot_answer_twice(): void
    {
        [, $classroom, $student] = $this->room();
        [$quiz, [$q1]] = $this->quiz($classroom);
        $payload = ['question_id' => $q1->id, 'option_id' => $this->opt($q1, true)->id];

        $this->actingAs($student)->post("/student/quizzes/{$quiz->id}/answer", $payload);
        $this->post("/student/quizzes/{$quiz->id}/answer", $payload)
            ->assertSessionHas('error', 'You have already answered this question.');
        $this->assertSame(1, Response::count());
    }

    public function test_cannot_answer_after_quiz_closes_or_before_start(): void
    {
        [, $classroom, $student] = $this->room();
        [$closed, [$cq]] = $this->quiz($classroom, 'closed');
        [$draft, [$dq]] = $this->quiz($classroom, 'draft');

        $this->actingAs($student)->post("/student/quizzes/{$closed->id}/answer", ['question_id' => $cq->id, 'option_id' => $this->opt($cq, true)->id])
            ->assertSessionHas('error', 'This round has ended. Your response was not accepted.');
        $this->post("/student/quizzes/{$draft->id}/answer", ['question_id' => $dq->id, 'option_id' => $this->opt($dq, true)->id])
            ->assertSessionHas('error', 'This quiz has not started yet.');
        $this->assertSame(0, Response::count());
    }

    public function test_option_from_another_question_is_rejected(): void
    {
        [, $classroom, $student] = $this->room();
        [$quiz, [$q1, $q2]] = $this->quiz($classroom);
        $this->actingAs($student)->post("/student/quizzes/{$quiz->id}/answer", ['question_id' => $q1->id, 'option_id' => $this->opt($q2, true)->id]);
        $this->assertSame(0, Response::count());
    }

    public function test_student_page_never_contains_correct_flag(): void
    {
        [, $classroom, $student] = $this->room();
        [$quiz] = $this->quiz($classroom);
        $html = $this->actingAs($student)->get("/student/quizzes/{$quiz->id}")->assertOk()->getContent();
        $this->assertStringNotContainsString('is_correct', $html);
    }

    // ---- Authorization ----
    public function test_faculty_cannot_access_another_faculty_classroom(): void
    {
        [, $classroom] = $this->room();
        [$quiz] = $this->quiz($classroom);
        $other = User::factory()->faculty()->create();
        $this->actingAs($other)->get("/faculty/classrooms/{$classroom->id}")->assertForbidden();
        $this->get("/faculty/quizzes/{$quiz->id}/results")->assertForbidden();
        $this->post("/faculty/quizzes/{$quiz->id}/start")->assertForbidden();
    }

    public function test_student_cannot_modify_quiz_questions(): void
    {
        [, $classroom, $student] = $this->room();
        [$quiz, [$q1]] = $this->quiz($classroom, 'draft');
        $this->actingAs($student)->post("/faculty/quizzes/{$quiz->id}/questions", [])->assertForbidden();
        $this->delete("/faculty/questions/{$q1->id}")->assertForbidden();
    }

    public function test_student_outside_classroom_cannot_access_quiz_or_results(): void
    {
        [, $classroom] = $this->room();
        [$quiz] = $this->quiz($classroom, 'closed');
        $outsider = User::factory()->student()->create();
        $this->actingAs($outsider)->get("/student/quizzes/{$quiz->id}")->assertForbidden();
        $this->get("/student/quizzes/{$quiz->id}/result")->assertForbidden();
        $this->get("/student/quizzes/{$quiz->id}/status")->assertForbidden();
    }

    // ---- Scoreboard ----
    public function test_scoreboard_scores_and_ranks_correctly(): void
    {
        [, $classroom, $s1] = $this->room();
        $s2 = User::factory()->student()->create();
        $s3 = User::factory()->student()->create();
        $classroom->students()->attach([$s2->id => ['display_name' => 'B'], $s3->id => ['display_name' => 'C']]);
        [$quiz, [$q1, $q2]] = $this->quiz($classroom);
        $svc = app(QuizScoringService::class);

        $svc->submitAnswer($quiz, $s2, $q1->id, $this->opt($q1, true)->id);   // s2: 2
        $svc->submitAnswer($quiz, $s2, $q2->id, $this->opt($q2, true)->id);   // s2: 3
        $svc->submitAnswer($quiz, $s1, $q1->id, $this->opt($q1, true)->id);   // s1: 2
        $svc->submitAnswer($quiz, $s1, $q2->id, $this->opt($q2, false)->id);  // s1: 2
        // s3 answers nothing

        $rows = $svc->scoreboard($quiz->fresh());
        $this->assertSame([$s2->id, $s1->id, $s3->id], $rows->pluck('student_id')->all());
        $this->assertSame([3, 2, 0], $rows->pluck('points')->all());
        $this->assertSame([1, 2, 3], $rows->pluck('rank')->all());
        $this->assertEquals(100.0, $rows->first()['percentage']);

        $summary = $svc->summary($quiz->fresh());
        $this->assertSame(3, $summary['stats']['total_students']);
        $this->assertEquals(round(5 / 3, 2), $summary['stats']['average_score']);
        $this->assertEquals(66.67, $summary['stats']['participation_rate']);
    }
}
