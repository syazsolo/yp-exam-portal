<?php

namespace Tests\Feature\Student;

use App\Console\Commands\CloseExams;
use App\Models\Answer;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\States\ExamSession\Invalid;
use App\States\ExamSession\Pending;
use App\States\ExamSession\Scored;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class ExamTakingTest extends TestCase
{
    use RefreshDatabase;

    private function studentWithExamAccess(array $examAttributes = []): array
    {
        $student = $this->createStudent();
        $class = SchoolClass::factory()->create();
        $subject = Subject::factory()->create();
        $exam = Exam::factory()->active()->mcqOnly()->create(array_merge([
            'subject_id' => $subject->id,
            'starts_at' => now()->subMinute(),
            'ends_at' => now()->addHour(),
            'time_limit_minutes' => 30,
        ], $examAttributes));

        $student->classes()->attach($class->id, ['assigned_at' => now()]);
        $class->subjects()->attach($subject->id);

        return [$student, $exam];
    }

    public function test_student_starts_exam_through_start_route(): void
    {
        [$student, $exam] = $this->studentWithExamAccess();

        $response = $this->actingAs($student)
            ->post("/student/exams/{$exam->id}/start");

        $session = ExamSession::where('exam_id', $exam->id)
            ->where('user_id', $student->id)
            ->sole();

        $response->assertRedirect("/student/exam-sessions/{$session->id}");
        $this->assertInstanceOf(Pending::class, $session->state);
        $this->assertNotNull($session->started_at);
    }

    public function test_student_cannot_start_exam_before_start_time(): void
    {
        [$student, $exam] = $this->studentWithExamAccess([
            'starts_at' => now()->addHour(),
            'ends_at' => now()->addHours(2),
        ]);

        $response = $this->actingAs($student)
            ->post("/student/exams/{$exam->id}/start");

        $response->assertForbidden();
        $this->assertDatabaseMissing('exam_sessions', [
            'exam_id' => $exam->id,
            'user_id' => $student->id,
        ]);
    }

    public function test_student_cannot_start_exam_after_end_time(): void
    {
        [$student, $exam] = $this->studentWithExamAccess([
            'starts_at' => now()->subHours(2),
            'ends_at' => now()->subMinute(),
        ]);

        $response = $this->actingAs($student)
            ->post("/student/exams/{$exam->id}/start");

        $response->assertForbidden();
    }

    public function test_student_cannot_start_exam_outside_their_class(): void
    {
        $student = $this->createStudent();
        $class = SchoolClass::factory()->create();
        $exam = Exam::factory()->active()->create([
            'starts_at' => now()->subMinute(),
            'ends_at' => now()->addHour(),
        ]);

        $student->classes()->attach($class->id, ['assigned_at' => now()]);

        $response = $this->actingAs($student)
            ->post("/student/exams/{$exam->id}/start");

        $response->assertForbidden();
    }

    public function test_starting_existing_pending_attempt_returns_same_session(): void
    {
        [$student, $exam] = $this->studentWithExamAccess();
        $session = ExamSession::factory()->pending()->create([
            'exam_id' => $exam->id,
            'user_id' => $student->id,
            'started_at' => now()->subMinutes(5),
        ]);

        $response = $this->actingAs($student)
            ->post("/student/exams/{$exam->id}/start");

        $response->assertRedirect("/student/exam-sessions/{$session->id}");
        $this->assertSame(1, ExamSession::where('exam_id', $exam->id)->where('user_id', $student->id)->count());
    }

    public function test_starting_finished_attempt_is_blocked(): void
    {
        [$student, $exam] = $this->studentWithExamAccess();
        ExamSession::factory()->scored()->create([
            'exam_id' => $exam->id,
            'user_id' => $student->id,
        ]);

        $response = $this->actingAs($student)
            ->post("/student/exams/{$exam->id}/start");

        $response->assertConflict();
    }

    public function test_pending_session_renders_live_taking_page(): void
    {
        [$student, $exam] = $this->studentWithExamAccess();
        $question = $exam->questions()->first();
        $option = $question->options()->first();
        $session = ExamSession::factory()->pending()->create([
            'exam_id' => $exam->id,
            'user_id' => $student->id,
            'started_at' => now()->subMinutes(10),
        ]);
        Answer::factory()->mcq()->create([
            'exam_session_id' => $session->id,
            'question_id' => $question->id,
            'selected_option_id' => $option->id,
        ]);

        $response = $this->actingAs($student)
            ->get("/student/exam-sessions/{$session->id}");

        $response->assertInertia(fn ($page) => $page
            ->component('Student/ExamSessions/Take')
            ->where('session.id', $session->id)
            ->where('session.exam.id', $exam->id)
            ->has('session.deadline')
            ->has('session.exam.questions', 2)
            ->where("answeredMap.{$question->id}.selected_option_id", $option->id));
    }

    public function test_non_pending_sessions_render_read_only_result_page(): void
    {
        foreach (['submitted', 'pending_review', 'scored', 'invalid'] as $state) {
            [$student, $exam] = $this->studentWithExamAccess();
            $session = ExamSession::factory()->create([
                'exam_id' => $exam->id,
                'user_id' => $student->id,
                'state' => $state,
                'submitted_at' => now(),
            ]);

            $this->actingAs($student)
                ->get("/student/exam-sessions/{$session->id}")
                ->assertInertia(fn ($page) => $page
                    ->component('Student/ExamSessions/Show')
                    ->where('session.id', $session->id)
                    ->where('session.state', $state));
        }
    }

    public function test_student_cannot_view_another_students_session(): void
    {
        $student = $this->createStudent();
        $other = $this->createStudent();
        $session = ExamSession::factory()->pending()->create(['user_id' => $other->id]);

        $response = $this->actingAs($student)
            ->get("/student/exam-sessions/{$session->id}");

        $response->assertForbidden();
    }

    public function test_student_can_save_answer_before_deadline(): void
    {
        [$student, $exam] = $this->studentWithExamAccess();
        $question = $exam->questions()->first();
        $option = $question->options()->first();
        $session = ExamSession::factory()->pending()->create([
            'exam_id' => $exam->id,
            'user_id' => $student->id,
            'started_at' => now()->subMinutes(5),
        ]);

        $response = $this->actingAs($student)
            ->post("/student/exam-sessions/{$session->id}/answers", [
                'question_id' => $question->id,
                'selected_option_id' => $option->id,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('answers', [
            'exam_session_id' => $session->id,
            'question_id' => $question->id,
            'selected_option_id' => $option->id,
        ]);
    }

    public function test_student_cannot_save_answer_after_deadline(): void
    {
        [$student, $exam] = $this->studentWithExamAccess(['time_limit_minutes' => 30]);
        $question = $exam->questions()->first();
        $option = $question->options()->first();
        $session = ExamSession::factory()->pending()->create([
            'exam_id' => $exam->id,
            'user_id' => $student->id,
            'started_at' => now()->subMinutes(31),
        ]);

        $response = $this->actingAs($student)
            ->post("/student/exam-sessions/{$session->id}/answers", [
                'question_id' => $question->id,
                'selected_option_id' => $option->id,
            ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('answers', [
            'exam_session_id' => $session->id,
            'question_id' => $question->id,
        ]);
    }

    public function test_student_cannot_save_option_from_another_question(): void
    {
        [$student, $exam] = $this->studentWithExamAccess();
        [$question, $otherQuestion] = $exam->questions()->get();
        $otherOption = $otherQuestion->options()->first();
        $session = ExamSession::factory()->pending()->create([
            'exam_id' => $exam->id,
            'user_id' => $student->id,
        ]);

        $response = $this->actingAs($student)
            ->post("/student/exam-sessions/{$session->id}/answers", [
                'question_id' => $question->id,
                'selected_option_id' => $otherOption->id,
            ]);

        $response->assertSessionHasErrors('selected_option_id');
        $this->assertDatabaseMissing('answers', [
            'exam_session_id' => $session->id,
            'question_id' => $question->id,
        ]);
    }

    public function test_student_cannot_save_answer_to_another_students_session(): void
    {
        [$student, $exam] = $this->studentWithExamAccess();
        $other = $this->createStudent();
        $question = $exam->questions()->first();
        $option = $question->options()->first();
        $session = ExamSession::factory()->pending()->create([
            'exam_id' => $exam->id,
            'user_id' => $other->id,
        ]);

        $response = $this->actingAs($student)
            ->post("/student/exam-sessions/{$session->id}/answers", [
                'question_id' => $question->id,
                'selected_option_id' => $option->id,
            ]);

        $response->assertForbidden();
    }

    public function test_browser_auto_submit_is_accepted_within_grace_buffer(): void
    {
        Config::set('exam_sessions.submit_grace_seconds', 10);
        [$student, $exam] = $this->studentWithExamAccess(['time_limit_minutes' => 30]);
        $session = ExamSession::factory()->pending()->create([
            'exam_id' => $exam->id,
            'user_id' => $student->id,
            'started_at' => now()->subMinutes(30)->subSeconds(5),
        ]);

        $response = $this->actingAs($student)
            ->post("/student/exam-sessions/{$session->id}/submit", [
                'auto_submitted' => true,
            ]);

        $response->assertRedirect("/student/exam-sessions/{$session->id}");
        $this->assertInstanceOf(Scored::class, $session->fresh()->state);
    }

    public function test_student_cannot_submit_when_any_mcq_question_is_unanswered(): void
    {
        [$student, $exam] = $this->studentWithExamAccess();
        $answeredQuestion = $exam->questions()->first();
        $option = $answeredQuestion->options()->first();
        $session = ExamSession::factory()->pending()->create([
            'exam_id' => $exam->id,
            'user_id' => $student->id,
        ]);
        Answer::factory()->mcq()->create([
            'exam_session_id' => $session->id,
            'question_id' => $answeredQuestion->id,
            'selected_option_id' => $option->id,
        ]);

        $response = $this->actingAs($student)
            ->post("/student/exam-sessions/{$session->id}/submit");

        $response->assertSessionHasErrors('answers');
        $this->assertInstanceOf(Pending::class, $session->fresh()->state);
    }

    public function test_student_can_submit_when_all_mcq_questions_are_answered(): void
    {
        [$student, $exam] = $this->studentWithExamAccess();
        $session = ExamSession::factory()->pending()->create([
            'exam_id' => $exam->id,
            'user_id' => $student->id,
        ]);

        $exam->questions()->with('options')->get()->each(function ($question) use ($session) {
            Answer::factory()->mcq()->create([
                'exam_session_id' => $session->id,
                'question_id' => $question->id,
                'selected_option_id' => $question->options->first()->id,
            ]);
        });

        $response = $this->actingAs($student)
            ->post("/student/exam-sessions/{$session->id}/submit");

        $response->assertRedirect("/student/exam-sessions/{$session->id}");
        $this->assertInstanceOf(Scored::class, $session->fresh()->state);
    }

    public function test_very_late_submit_auto_submits_saved_answers_without_marking_invalid(): void
    {
        Config::set('exam_sessions.submit_grace_seconds', 10);
        [$student, $exam] = $this->studentWithExamAccess(['time_limit_minutes' => 30]);
        $session = ExamSession::factory()->pending()->create([
            'exam_id' => $exam->id,
            'user_id' => $student->id,
            'started_at' => now()->subMinutes(31),
        ]);

        $response = $this->actingAs($student)
            ->post("/student/exam-sessions/{$session->id}/submit");

        $response->assertRedirect("/student/exam-sessions/{$session->id}");
        $this->assertInstanceOf(Scored::class, $session->fresh()->state);
        $this->assertNotInstanceOf(Invalid::class, $session->fresh()->state);
    }

    public function test_close_exams_command_auto_submits_abandoned_sessions_after_personal_deadline_grace(): void
    {
        Config::set('exam_sessions.submit_grace_seconds', 10);
        [$student, $exam] = $this->studentWithExamAccess([
            'starts_at' => now()->subHours(2),
            'ends_at' => now()->addHour(),
            'time_limit_minutes' => 30,
        ]);
        $session = ExamSession::factory()->pending()->create([
            'exam_id' => $exam->id,
            'user_id' => $student->id,
            'started_at' => now()->subMinutes(31),
        ]);

        Artisan::call(CloseExams::class);

        $this->assertInstanceOf(Scored::class, $session->fresh()->state);
    }

    public function test_close_exams_command_does_not_submit_sessions_still_inside_grace_buffer(): void
    {
        Config::set('exam_sessions.submit_grace_seconds', 10);
        [$student, $exam] = $this->studentWithExamAccess([
            'time_limit_minutes' => 30,
        ]);
        $session = ExamSession::factory()->pending()->create([
            'exam_id' => $exam->id,
            'user_id' => $student->id,
            'started_at' => now()->subMinutes(30)->subSeconds(5),
        ]);

        Artisan::call(CloseExams::class);

        $this->assertInstanceOf(Pending::class, $session->fresh()->state);
    }
}
