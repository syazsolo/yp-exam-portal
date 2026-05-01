<?php

namespace Tests\Unit;

use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\SchoolClass;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditTrailTest extends TestCase
{
    use RefreshDatabase;

    // --- Class events ---

    public function test_creating_a_class_logs_an_audit_entry(): void
    {
        $class = SchoolClass::factory()->create();

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'created',
            'auditable_type' => SchoolClass::class,
            'auditable_id' => $class->id,
        ]);
    }

    public function test_soft_deleting_a_class_logs_an_audit_entry(): void
    {
        $class = SchoolClass::factory()->create();
        $class->delete();

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'deleted',
            'auditable_type' => SchoolClass::class,
            'auditable_id' => $class->id,
        ]);
    }

    // --- Enrollment events ---

    public function test_assigning_student_to_class_logs_an_audit_entry(): void
    {
        $student = $this->createStudent();
        $class = SchoolClass::factory()->create();

        $student->classes()->attach($class->id, ['assigned_at' => now()]);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'student.enrolled',
            'subject_id' => $student->id,
            'auditable_type' => SchoolClass::class,
            'auditable_id' => $class->id,
        ]);
    }

    // --- Exam events ---

    public function test_creating_an_exam_logs_an_audit_entry(): void
    {
        $exam = Exam::factory()->create();

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'created',
            'auditable_type' => Exam::class,
            'auditable_id' => $exam->id,
        ]);
    }

    public function test_soft_deleting_an_exam_logs_an_audit_entry(): void
    {
        $exam = Exam::factory()->create();
        $exam->delete();

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'deleted',
            'auditable_type' => Exam::class,
            'auditable_id' => $exam->id,
        ]);
    }

    // --- Exam session events ---

    public function test_starting_an_exam_session_logs_an_audit_entry(): void
    {
        $session = ExamSession::factory()->create();

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'created',
            'auditable_type' => ExamSession::class,
            'auditable_id' => $session->id,
        ]);
    }

    public function test_submitting_an_exam_session_logs_an_audit_entry(): void
    {
        $session = ExamSession::factory()->create();
        $session->submit();

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'session.submitted',
            'auditable_type' => ExamSession::class,
            'auditable_id' => $session->id,
        ]);
    }

    // --- Scoring events ---

    public function test_lecturer_scoring_an_open_text_answer_logs_an_audit_entry(): void
    {
        $lecturer = $this->createLecturer();
        $session = ExamSession::factory()->pendingReview()->create();
        $answer = $session->answers()->whereHas('question', fn ($q) => $q->openText())->first();

        $this->actingAs($lecturer);
        $answer->assignScore(3.0);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'answer.scored',
            'auditable_type' => get_class($answer),
            'auditable_id' => $answer->id,
            'causer_id' => $lecturer->id,
        ]);
    }
}
