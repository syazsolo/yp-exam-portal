<?php

namespace Tests\Unit;

use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\Question;
use App\States\ExamSession\Invalid;
use App\States\ExamSession\Pending;
use App\States\ExamSession\PendingReview;
use App\States\ExamSession\Scored;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExamSessionStateTest extends TestCase
{
    use RefreshDatabase;

    // --- Initial state ---

    public function test_session_starts_in_pending_state(): void
    {
        $session = ExamSession::factory()->create();

        $this->assertInstanceOf(Pending::class, $session->state);
    }

    // --- MCQ-only flow ---

    public function test_mcq_only_session_transitions_to_scored_on_submission(): void
    {
        $exam = Exam::factory()->mcqOnly()->create();
        $session = ExamSession::factory()->create(['exam_id' => $exam->id]);

        $session->submit();

        $this->assertInstanceOf(Scored::class, $session->fresh()->state);
    }

    public function test_mcq_only_session_does_not_require_lecturer_review(): void
    {
        $exam = Exam::factory()->mcqOnly()->create();
        $session = ExamSession::factory()->create(['exam_id' => $exam->id]);

        $session->submit();

        $this->assertNotInstanceOf(PendingReview::class, $session->fresh()->state);
    }

    // --- Open-text flow ---

    public function test_session_with_open_text_question_transitions_to_pending_review_on_submission(): void
    {
        $exam = Exam::factory()->withOpenText()->create();
        $session = ExamSession::factory()->create(['exam_id' => $exam->id]);

        $session->submit();

        $this->assertInstanceOf(PendingReview::class, $session->fresh()->state);
    }

    public function test_session_moves_to_scored_after_all_open_text_questions_are_reviewed(): void
    {
        $exam = Exam::factory()->withOpenText()->create();
        $session = ExamSession::factory()->pendingReview()->create(['exam_id' => $exam->id]);

        $session->markAllReviewed();

        $this->assertInstanceOf(Scored::class, $session->fresh()->state);
    }

    // --- Time limit / end time edge case ---

    public function test_submitting_after_exam_end_time_marks_session_as_invalid(): void
    {
        $exam = Exam::factory()->create([
            'starts_at' => now()->subHour(),
            'ends_at' => now()->subMinute(), // already ended
        ]);
        $session = ExamSession::factory()->create([
            'exam_id' => $exam->id,
            'started_at' => now()->subHour(),
        ]);

        // auto-submit job failed to run; student manually tries to submit
        $session->submit();

        $this->assertInstanceOf(Invalid::class, $session->fresh()->state);
    }

    // --- Lecturer scoring ---

    public function test_lecturer_cannot_assign_score_exceeding_question_weight(): void
    {
        $question = Question::factory()->openText()->create(['weight' => 5.0]);

        $this->expectException(\InvalidArgumentException::class);
        $question->validateScore(6.0);
    }

    public function test_lecturer_cannot_assign_negative_score(): void
    {
        $question = Question::factory()->openText()->create(['weight' => 5.0]);

        $this->expectException(\InvalidArgumentException::class);
        $question->validateScore(-1.0);
    }
}
