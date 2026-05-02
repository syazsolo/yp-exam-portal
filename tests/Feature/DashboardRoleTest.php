<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\ExamSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_lecturer_dashboard_exposes_lecturer_role(): void
    {
        $user = $this->createLecturer();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('auth.user.role', 'lecturer')
        );
    }

    public function test_student_dashboard_exposes_student_role(): void
    {
        $user = $this->createStudent();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('auth.user.role', 'student')
        );
    }

    public function test_lecturer_dashboard_counts_their_pending_review_sessions(): void
    {
        $lecturer = $this->createLecturer();
        $otherLecturer = $this->createLecturer();

        $ownExam = Exam::factory()->create(['created_by' => $lecturer->id]);
        $otherExam = Exam::factory()->create(['created_by' => $otherLecturer->id]);

        ExamSession::factory()->create([
            'exam_id' => $ownExam->id,
            'state' => 'pending_review',
            'submitted_at' => now(),
        ]);
        ExamSession::factory()->create([
            'exam_id' => $ownExam->id,
            'state' => 'scored',
            'submitted_at' => now(),
        ]);
        ExamSession::factory()->create([
            'exam_id' => $otherExam->id,
            'state' => 'pending_review',
            'submitted_at' => now(),
        ]);

        $response = $this->actingAs($lecturer)->get('/lecturer');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Lecturer/Dashboard')
            ->where('pendingReviews', 1)
        );
    }
}
