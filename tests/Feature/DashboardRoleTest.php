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

    public function test_lecturer_dashboard_landing_redirects_to_lecturer_dashboard(): void
    {
        $user = $this->createLecturer();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertRedirect(route('lecturer.dashboard'));
    }

    public function test_student_dashboard_landing_redirects_to_student_dashboard(): void
    {
        $user = $this->createStudent();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertRedirect(route('student.dashboard'));
    }

    public function test_lecturer_pages_receive_lecturer_navigation(): void
    {
        $user = $this->createLecturer();

        $response = $this->actingAs($user)->get('/lecturer');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Lecturer/Dashboard')
            ->has('app.nav', 4)
            ->where('app.nav.0.label', 'Overview')
            ->where('app.nav.0.href', route('lecturer.dashboard'))
            ->where('app.nav.0.match', 'lecturer.dashboard')
            ->where('app.nav.0.icon', 'home')
            ->where('app.nav.1.label', 'Exams')
            ->where('app.nav.1.href', route('lecturer.exams.index'))
            ->where('app.nav.1.match', 'lecturer.exams.*')
            ->where('app.nav.1.icon', 'doc')
        );
    }

    public function test_student_pages_receive_student_navigation(): void
    {
        $user = $this->createStudent();

        $response = $this->actingAs($user)->get('/student');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Student/Dashboard')
            ->has('app.nav', 2)
            ->where('app.nav.0.label', 'Dashboard')
            ->where('app.nav.0.href', route('student.dashboard'))
            ->where('app.nav.0.match', 'student.dashboard')
            ->where('app.nav.0.icon', 'home')
            ->where('app.nav.1.label', 'Available Exams')
            ->where('app.nav.1.href', route('student.exams.index'))
            ->where('app.nav.1.match', 'student.exams.*')
            ->where('app.nav.1.icon', 'doc')
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
