<?php

namespace Tests\Feature\Lecturer;

use App\Models\Answer;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\Question;
use App\Models\Subject;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ExamCreationTest extends TestCase
{
    use RefreshDatabase;

    // --- Create exam ---

    public function test_lecturer_can_create_an_exam_for_a_subject(): void
    {
        $lecturer = $this->createLecturer();
        $subject = Subject::factory()->create(['created_by' => $lecturer->id]);

        $response = $this->actingAs($lecturer)
            ->post("/lecturer/subjects/{$subject->id}/exams", [
                'title' => 'Midterm 1',
                'time_limit_minutes' => 60,
                'starts_at' => now()->addDay()->toDateTimeString(),
                'ends_at' => now()->addDay()->addHours(2)->toDateTimeString(),
                'default_question_weight' => 1.0,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('exams', [
            'subject_id' => $subject->id,
            'title' => 'Midterm 1',
            'time_limit_minutes' => 60,
            'default_question_weight' => 1.0,
        ]);
    }

    public function test_student_cannot_create_an_exam(): void
    {
        $student = $this->createStudent();
        $subject = Subject::factory()->create();

        $response = $this->actingAs($student)
            ->post("/lecturer/subjects/{$subject->id}/exams", [
                'title' => 'Midterm 1',
                'time_limit_minutes' => 60,
                'starts_at' => now()->addDay()->toDateTimeString(),
                'ends_at' => now()->addDay()->addHours(2)->toDateTimeString(),
            ]);

        $response->assertForbidden();
    }

    public function test_exam_end_time_must_be_after_start_time(): void
    {
        $lecturer = $this->createLecturer();
        $subject = Subject::factory()->create(['created_by' => $lecturer->id]);

        $response = $this->actingAs($lecturer)
            ->post("/lecturer/subjects/{$subject->id}/exams", [
                'title' => 'Bad Exam',
                'time_limit_minutes' => 30,
                'starts_at' => now()->addDay()->addHours(2)->toDateTimeString(),
                'ends_at' => now()->addDay()->toDateTimeString(), // before starts_at
            ]);

        $response->assertSessionHasErrors('ends_at');
    }

    public function test_exam_is_soft_deleted_and_retained_in_database(): void
    {
        $exam = Exam::factory()->create();
        $exam->delete();

        $this->assertSoftDeleted($exam);
        $this->assertNull(Exam::find($exam->id));
    }

    // --- MCQ questions ---

    public function test_lecturer_exam_show_exposes_session_state_for_frontend(): void
    {
        $lecturer = $this->createLecturer();
        $student = $this->createStudent();
        $exam = Exam::factory()->create(['created_by' => $lecturer->id]);

        ExamSession::factory()->create([
            'exam_id' => $exam->id,
            'user_id' => $student->id,
            'state' => 'pending_review',
            'submitted_at' => now(),
        ]);

        $response = $this->actingAs($lecturer)->get("/lecturer/exams/{$exam->id}");

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Lecturer/Exams/Show')
            ->where('sessions.0.state', 'pending_review')
            ->missing('sessions.0.status')
        );
    }

    public function test_lecturer_can_add_mcq_question_to_exam(): void
    {
        $lecturer = $this->createLecturer();
        $exam = Exam::factory()->create(['created_by' => $lecturer->id]);

        $response = $this->actingAs($lecturer)
            ->post("/lecturer/exams/{$exam->id}/questions", [
                'type' => 'mcq',
                'text' => 'What is 2 + 2?',
                'weight' => 2.0,
                'options' => [
                    ['text' => '3', 'is_correct' => false],
                    ['text' => '4', 'is_correct' => true],
                    ['text' => '5', 'is_correct' => false],
                ],
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('questions', [
            'exam_id' => $exam->id,
            'type' => 'mcq',
            'text' => 'What is 2 + 2?',
        ]);
    }

    public function test_mcq_question_requires_at_least_two_options(): void
    {
        $lecturer = $this->createLecturer();
        $exam = Exam::factory()->create(['created_by' => $lecturer->id]);

        $response = $this->actingAs($lecturer)
            ->post("/lecturer/exams/{$exam->id}/questions", [
                'type' => 'mcq',
                'text' => 'Only one option?',
                'options' => [
                    ['text' => 'Only one', 'is_correct' => true],
                ],
            ]);

        $response->assertSessionHasErrors('options');
    }

    public function test_mcq_question_requires_exactly_one_correct_option(): void
    {
        $lecturer = $this->createLecturer();
        $exam = Exam::factory()->create(['created_by' => $lecturer->id]);

        $response = $this->actingAs($lecturer)
            ->post("/lecturer/exams/{$exam->id}/questions", [
                'type' => 'mcq',
                'text' => 'Two correct?',
                'options' => [
                    ['text' => 'A', 'is_correct' => true],
                    ['text' => 'B', 'is_correct' => true],
                    ['text' => 'C', 'is_correct' => false],
                ],
            ]);

        $response->assertSessionHasErrors('options');
    }

    // --- Open-text questions ---

    public function test_lecturer_can_add_open_text_question_to_exam(): void
    {
        $lecturer = $this->createLecturer();
        $exam = Exam::factory()->create(['created_by' => $lecturer->id]);

        $response = $this->actingAs($lecturer)
            ->post("/lecturer/exams/{$exam->id}/questions", [
                'type' => 'open_text',
                'text' => 'Explain the concept of polymorphism.',
                'weight' => 10.0,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('questions', [
            'exam_id' => $exam->id,
            'type' => 'open_text',
        ]);
    }

    // --- Lecturer scoring open-text answers ---

    public function test_lecturer_can_assign_score_to_open_text_answer(): void
    {
        $lecturer = $this->createLecturer();
        $answer = $this->openTextAnswerOwnedBy($lecturer, 10.0);

        $response = $this->actingAs($lecturer)
            ->patch("/lecturer/answers/{$answer->id}/score", [
                'score' => 7.0,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('answers', [
            'id' => $answer->id,
            'score' => 7.0,
        ]);
    }

    public function test_lecturer_cannot_assign_score_above_question_weight(): void
    {
        $lecturer = $this->createLecturer();
        $answer = $this->openTextAnswerOwnedBy($lecturer, 5.0);

        $response = $this->actingAs($lecturer)
            ->patch("/lecturer/answers/{$answer->id}/score", [
                'score' => 99.0,
            ]);

        $response->assertSessionHasErrors('score');
    }

    public function test_lecturer_cannot_assign_negative_score(): void
    {
        $lecturer = $this->createLecturer();
        $answer = $this->openTextAnswerOwnedBy($lecturer, 5.0);

        $response = $this->actingAs($lecturer)
            ->patch("/lecturer/answers/{$answer->id}/score", [
                'score' => -1.0,
            ]);

        $response->assertSessionHasErrors('score');
    }

    private function openTextAnswerOwnedBy($lecturer, float $weight): Answer
    {
        $subject = Subject::factory()->create(['created_by' => $lecturer->id]);
        $exam = Exam::factory()->create([
            'created_by' => $lecturer->id,
            'subject_id' => $subject->id,
        ]);
        $question = Question::factory()->openText()->create([
            'exam_id' => $exam->id,
            'weight' => $weight,
        ]);
        $session = ExamSession::factory()->create([
            'exam_id' => $exam->id,
            'state' => 'pending_review',
            'submitted_at' => now(),
        ]);

        return Answer::factory()->openText()->create([
            'exam_session_id' => $session->id,
            'question_id' => $question->id,
        ]);
    }
}
