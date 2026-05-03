<?php

namespace Tests\Feature\Lecturer;

use App\Models\Answer;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\Question;
use App\Models\SchoolClass;
use App\Models\Subject;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OwnershipAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_lecturer_cannot_score_answer_from_another_lecturers_exam(): void
    {
        $lecturer = $this->createLecturer();
        $otherLecturer = $this->createLecturer();
        $exam = $this->examOwnedBy($otherLecturer);
        $question = Question::factory()->openText()->create([
            'exam_id' => $exam->id,
            'weight' => 10.0,
        ]);
        $session = ExamSession::factory()->create([
            'exam_id' => $exam->id,
            'state' => 'pending_review',
            'submitted_at' => now(),
        ]);
        $answer = Answer::factory()->openText()->create([
            'exam_session_id' => $session->id,
            'question_id' => $question->id,
            'score' => null,
        ]);

        $response = $this->actingAs($lecturer)
            ->patch(route('lecturer.answers.score', $answer), [
                'score' => 7.0,
            ]);

        $response->assertForbidden();
        $this->assertDatabaseHas('answers', [
            'id' => $answer->id,
            'score' => null,
        ]);
    }

    public function test_lecturer_can_score_answer_from_their_own_exam(): void
    {
        $lecturer = $this->createLecturer();
        $exam = $this->examOwnedBy($lecturer);
        $question = Question::factory()->openText()->create([
            'exam_id' => $exam->id,
            'weight' => 10.0,
        ]);
        $session = ExamSession::factory()->create([
            'exam_id' => $exam->id,
            'state' => 'pending_review',
            'submitted_at' => now(),
        ]);
        $answer = Answer::factory()->openText()->create([
            'exam_session_id' => $session->id,
            'question_id' => $question->id,
        ]);

        $response = $this->actingAs($lecturer)
            ->patch(route('lecturer.answers.score', $answer), [
                'score' => 7.0,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('answers', [
            'id' => $answer->id,
            'score' => 7.0,
        ]);
    }

    public function test_lecturer_cannot_view_another_lecturers_exam(): void
    {
        $lecturer = $this->createLecturer();
        $otherLecturer = $this->createLecturer();
        $exam = $this->examOwnedBy($otherLecturer);

        $response = $this->actingAs($lecturer)
            ->get(route('lecturer.exams.show', $exam));

        $response->assertForbidden();
    }

    public function test_lecturer_cannot_create_exam_using_foreign_subject_nested_route(): void
    {
        $lecturer = $this->createLecturer();
        $otherLecturer = $this->createLecturer();
        $foreignSubject = Subject::factory()->create(['created_by' => $otherLecturer->id]);

        $response = $this->actingAs($lecturer)
            ->post(route('lecturer.subjects.exams.store', $foreignSubject), $this->validExamPayload());

        $response->assertForbidden();
        $this->assertDatabaseMissing('exams', [
            'subject_id' => $foreignSubject->id,
            'created_by' => $lecturer->id,
        ]);
    }

    public function test_lecturer_cannot_create_exam_using_foreign_subject_id(): void
    {
        $lecturer = $this->createLecturer();
        $otherLecturer = $this->createLecturer();
        $foreignSubject = Subject::factory()->create(['created_by' => $otherLecturer->id]);

        $response = $this->actingAs($lecturer)
            ->post(route('lecturer.exams.store'), [
                ...$this->validExamPayload(),
                'subject_id' => $foreignSubject->id,
            ]);

        $response->assertSessionHasErrors('subject_id');
        $this->assertDatabaseMissing('exams', [
            'subject_id' => $foreignSubject->id,
            'created_by' => $lecturer->id,
        ]);
    }

    public function test_lecturer_cannot_update_exam_to_foreign_subject(): void
    {
        $lecturer = $this->createLecturer();
        $otherLecturer = $this->createLecturer();
        $ownedSubject = Subject::factory()->create(['created_by' => $lecturer->id]);
        $foreignSubject = Subject::factory()->create(['created_by' => $otherLecturer->id]);
        $exam = Exam::factory()->create([
            'created_by' => $lecturer->id,
            'subject_id' => $ownedSubject->id,
        ]);

        $response = $this->actingAs($lecturer)
            ->patch(route('lecturer.exams.update', $exam), [
                ...$this->validExamPayload(),
                'subject_id' => $foreignSubject->id,
            ]);

        $response->assertSessionHasErrors('subject_id');
        $this->assertSame($ownedSubject->id, $exam->fresh()->subject_id);
    }

    public function test_lecturer_cannot_attach_foreign_subject_when_creating_class(): void
    {
        $lecturer = $this->createLecturer();
        $otherLecturer = $this->createLecturer();
        $foreignSubject = Subject::factory()->create(['created_by' => $otherLecturer->id]);

        $response = $this->actingAs($lecturer)
            ->post(route('lecturer.classes.store'), [
                'name' => 'Class A1',
                'subject_ids' => [$foreignSubject->id],
            ]);

        $response->assertSessionHasErrors('subject_ids.0');
        $this->assertDatabaseMissing('class_subject', [
            'subject_id' => $foreignSubject->id,
        ]);
    }

    public function test_lecturer_cannot_sync_foreign_subject_when_updating_class(): void
    {
        $lecturer = $this->createLecturer();
        $otherLecturer = $this->createLecturer();
        $ownedSubject = Subject::factory()->create(['created_by' => $lecturer->id]);
        $foreignSubject = Subject::factory()->create(['created_by' => $otherLecturer->id]);
        $class = SchoolClass::factory()->create(['created_by' => $lecturer->id]);
        $class->subjects()->attach($ownedSubject->id);

        $response = $this->actingAs($lecturer)
            ->patch(route('lecturer.classes.update', $class), [
                'name' => 'Class A1 Updated',
                'subject_ids' => [$foreignSubject->id],
            ]);

        $response->assertSessionHasErrors('subject_ids.0');
        $this->assertDatabaseHas('class_subject', [
            'class_id' => $class->id,
            'subject_id' => $ownedSubject->id,
        ]);
        $this->assertDatabaseMissing('class_subject', [
            'class_id' => $class->id,
            'subject_id' => $foreignSubject->id,
        ]);
    }

    public function test_lecturer_cannot_review_foreign_session(): void
    {
        $lecturer = $this->createLecturer();
        $otherLecturer = $this->createLecturer();
        $session = ExamSession::factory()->create([
            'exam_id' => $this->examOwnedBy($otherLecturer)->id,
            'state' => 'pending_review',
            'submitted_at' => now(),
        ]);

        $response = $this->actingAs($lecturer)
            ->get(route('lecturer.sessions.review', $session));

        $response->assertForbidden();
    }

    public function test_lecturer_cannot_finalize_foreign_session(): void
    {
        $lecturer = $this->createLecturer();
        $otherLecturer = $this->createLecturer();
        $session = ExamSession::factory()->create([
            'exam_id' => $this->examOwnedBy($otherLecturer)->id,
            'state' => 'pending_review',
            'submitted_at' => now(),
        ]);

        $response = $this->actingAs($lecturer)
            ->post(route('lecturer.sessions.finalize', $session));

        $response->assertForbidden();
    }

    private function examOwnedBy($lecturer): Exam
    {
        $subject = Subject::factory()->create(['created_by' => $lecturer->id]);

        return Exam::factory()->create([
            'created_by' => $lecturer->id,
            'subject_id' => $subject->id,
        ]);
    }

    private function validExamPayload(): array
    {
        return [
            'title' => 'Midterm 1',
            'time_limit_minutes' => 60,
            'starts_at' => now()->addDay()->toDateTimeString(),
            'ends_at' => now()->addDay()->addHours(2)->toDateTimeString(),
            'default_question_weight' => 1.0,
        ];
    }
}
