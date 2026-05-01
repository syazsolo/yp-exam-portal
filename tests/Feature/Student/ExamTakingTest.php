<?php

namespace Tests\Feature\Student;

use App\Console\Commands\CloseExams;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use App\States\ExamSession\Invalid;
use App\States\ExamSession\Pending;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ExamTakingTest extends TestCase
{
    use RefreshDatabase;

    private function studentWithExamAccess(): array
    {
        $student = $this->createStudent();
        $class = SchoolClass::factory()->create();
        $subject = Subject::factory()->create();
        $exam = Exam::factory()->create(['subject_id' => $subject->id]);

        $student->classes()->attach($class->id, ['assigned_at' => now()]);
        $class->subjects()->attach($subject->id);

        return [$student, $exam];
    }

    // --- Start exam ---

    public function test_student_can_start_exam_at_its_start_time(): void
    {
        [$student, $exam] = $this->studentWithExamAccess();
        $exam->update(['starts_at' => now()->subMinute(), 'ends_at' => now()->addHour()]);

        $response = $this->actingAs($student)
            ->post("/student/exams/{$exam->id}/sessions");

        $response->assertRedirect();
        $this->assertDatabaseHas('exam_sessions', [
            'exam_id' => $exam->id,
            'user_id' => $student->id,
        ]);
    }

    public function test_student_cannot_start_exam_before_start_time(): void
    {
        [$student, $exam] = $this->studentWithExamAccess();
        $exam->update(['starts_at' => now()->addHour(), 'ends_at' => now()->addHours(2)]);

        $response = $this->actingAs($student)
            ->post("/student/exams/{$exam->id}/sessions");

        $response->assertForbidden();
        $this->assertDatabaseMissing('exam_sessions', [
            'exam_id' => $exam->id,
            'user_id' => $student->id,
        ]);
    }

    public function test_student_cannot_start_exam_after_end_time(): void
    {
        [$student, $exam] = $this->studentWithExamAccess();
        $exam->update(['starts_at' => now()->subHours(2), 'ends_at' => now()->subMinute()]);

        $response = $this->actingAs($student)
            ->post("/student/exams/{$exam->id}/sessions");

        $response->assertForbidden();
    }

    public function test_starting_exam_creates_an_exam_session(): void
    {
        [$student, $exam] = $this->studentWithExamAccess();
        $exam->update(['starts_at' => now()->subMinute(), 'ends_at' => now()->addHour()]);

        $this->actingAs($student)
            ->post("/student/exams/{$exam->id}/sessions");

        $this->assertDatabaseHas('exam_sessions', [
            'exam_id' => $exam->id,
            'user_id' => $student->id,
        ]);
    }

    public function test_student_cannot_have_two_active_sessions_for_same_exam(): void
    {
        [$student, $exam] = $this->studentWithExamAccess();
        $exam->update(['starts_at' => now()->subMinute(), 'ends_at' => now()->addHour()]);

        $this->actingAs($student)->post("/student/exams/{$exam->id}/sessions");

        $response = $this->actingAs($student)
            ->post("/student/exams/{$exam->id}/sessions");

        $response->assertConflict();
        $this->assertCount(1, ExamSession::where([
            'exam_id' => $exam->id,
            'user_id' => $student->id,
        ])->get());
    }

    // --- Submit ---

    public function test_student_can_submit_session_before_time_limit_expires(): void
    {
        [$student, $exam] = $this->studentWithExamAccess();
        $exam->update([
            'starts_at' => now()->subMinute(),
            'ends_at' => now()->addHour(),
            'time_limit_minutes' => 60,
        ]);

        $session = ExamSession::factory()->create([
            'exam_id' => $exam->id,
            'user_id' => $student->id,
            'started_at' => now()->subMinutes(10),
        ]);

        $response = $this->actingAs($student)
            ->post("/student/exam-sessions/{$session->id}/submit");

        $response->assertRedirect();
        $this->assertNotInstanceOf(Pending::class, $session->fresh()->state);
    }

    public function test_submission_after_time_limit_marks_session_as_invalid(): void
    {
        [$student, $exam] = $this->studentWithExamAccess();
        $exam->update([
            'starts_at' => now()->subHours(2),
            'ends_at' => now()->addHour(),
            'time_limit_minutes' => 30,
        ]);

        $session = ExamSession::factory()->create([
            'exam_id' => $exam->id,
            'user_id' => $student->id,
            'started_at' => now()->subHours(2), // started 2 hrs ago, limit is 30 mins
        ]);

        $response = $this->actingAs($student)
            ->post("/student/exam-sessions/{$session->id}/submit");

        $response->assertRedirect();
        $this->assertInstanceOf(Invalid::class, $session->fresh()->state);
    }

    // --- Auto-submit job ---

    public function test_close_exams_job_submits_all_pending_sessions_when_exam_ends(): void
    {
        [$student, $exam] = $this->studentWithExamAccess();
        $exam->update([
            'starts_at' => now()->subHours(2),
            'ends_at' => now()->subMinute(), // just ended
            'time_limit_minutes' => 60,
        ]);

        $session = ExamSession::factory()->pending()->create([
            'exam_id' => $exam->id,
            'user_id' => $student->id,
            'started_at' => now()->subHours(2),
        ]);

        Artisan::call(CloseExams::class);

        $this->assertNotInstanceOf(Pending::class, $session->fresh()->state);
    }

    public function test_close_exams_job_affects_all_ongoing_sessions_for_ended_exam(): void
    {
        $subject = Subject::factory()->create();
        $exam = Exam::factory()->create([
            'subject_id' => $subject->id,
            'starts_at' => now()->subHours(2),
            'ends_at' => now()->subMinute(),
            'time_limit_minutes' => 60,
        ]);

        $class = SchoolClass::factory()->create();
        $class->subjects()->attach($subject->id);

        $students = User::factory()->student()->count(3)->create();
        foreach ($students as $student) {
            $student->classes()->attach($class->id, ['assigned_at' => now()->subDay()]);
            ExamSession::factory()->pending()->create([
                'exam_id' => $exam->id,
                'user_id' => $student->id,
                'started_at' => now()->subHours(2),
            ]);
        }

        Artisan::call(CloseExams::class);

        $stillPending = ExamSession::where('exam_id', $exam->id)
            ->whereState('state', Pending::class)
            ->count();

        $this->assertSame(0, $stillPending);
    }
}
