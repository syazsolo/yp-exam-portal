<?php

namespace Tests\Feature\Student;

use App\Models\ExamSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExamHistoryTest extends TestCase
{
    use RefreshDatabase;

    // --- List past sessions ---

    public function test_student_can_list_their_past_exam_sessions(): void
    {
        $student = $this->createStudent();
        ExamSession::factory()->scored()->count(3)->create(['user_id' => $student->id]);

        $response = $this->actingAs($student)->get('/student/exam-sessions');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->has('sessions', 3));
    }

    public function test_student_only_sees_their_own_sessions_in_list(): void
    {
        $student = $this->createStudent();
        $other = $this->createStudent();

        ExamSession::factory()->scored()->count(2)->create(['user_id' => $student->id]);
        ExamSession::factory()->scored()->count(5)->create(['user_id' => $other->id]);

        $response = $this->actingAs($student)->get('/student/exam-sessions');

        $response->assertInertia(fn ($page) => $page->has('sessions', 2));
    }

    // --- View single past session ---

    public function test_student_can_view_their_past_exam_session(): void
    {
        $student = $this->createStudent();
        $session = ExamSession::factory()->scored()->create(['user_id' => $student->id]);

        $response = $this->actingAs($student)
            ->get("/student/exam-sessions/{$session->id}");

        $response->assertStatus(200);
    }

    public function test_student_can_view_answers_they_submitted_in_a_past_session(): void
    {
        $student = $this->createStudent();
        $session = ExamSession::factory()->scored()->withAnswers()->create(['user_id' => $student->id]);

        $response = $this->actingAs($student)
            ->get("/student/exam-sessions/{$session->id}");

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->has('session.answers'));
    }

    public function test_student_can_view_score_for_a_scored_session(): void
    {
        $student = $this->createStudent();
        $session = ExamSession::factory()->scored()->create(['user_id' => $student->id]);

        $response = $this->actingAs($student)
            ->get("/student/exam-sessions/{$session->id}");

        $response->assertInertia(fn ($page) => $page->has('session.score'));
    }

    public function test_student_cannot_view_another_students_session(): void
    {
        $student = $this->createStudent();
        $other = $this->createStudent();
        $session = ExamSession::factory()->scored()->create(['user_id' => $other->id]);

        $response = $this->actingAs($student)
            ->get("/student/exam-sessions/{$session->id}");

        $response->assertForbidden();
    }
}
