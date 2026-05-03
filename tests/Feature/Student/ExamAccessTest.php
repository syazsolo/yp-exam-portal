<?php

namespace Tests\Feature\Student;

use App\Models\Exam;
use App\Models\SchoolClass;
use App\Models\Subject;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExamAccessTest extends TestCase
{
    use RefreshDatabase;

    // --- Student with class ---

    public function test_student_can_list_active_exam_when_subject_is_in_their_class(): void
    {
        $student = $this->createStudent();
        $class = SchoolClass::factory()->create();
        $subject = Subject::factory()->create();
        $exam = Exam::factory()->active()->create(['subject_id' => $subject->id]);

        $student->classes()->attach($class->id, ['assigned_at' => now()]);
        $class->subjects()->attach($subject->id);

        $response = $this->actingAs($student)
            ->get('/student/exams');

        $response->assertInertia(fn ($page) => $page
            ->component('Student/Exams/Index')
            ->has('exams', 1)
            ->where('exams.0.id', $exam->id)
            ->where('exams.0.attempt_state', null)
            ->where('exams.0.session_id', null));
    }

    public function test_student_does_not_list_exam_when_subject_is_not_in_their_class(): void
    {
        $student = $this->createStudent();
        $class = SchoolClass::factory()->create();
        $otherSubject = Subject::factory()->create();
        Exam::factory()->active()->create(['subject_id' => $otherSubject->id]);

        $student->classes()->attach($class->id, ['assigned_at' => now()]);

        $response = $this->actingAs($student)
            ->get('/student/exams');

        $response->assertInertia(fn ($page) => $page->has('exams', 0));
    }

    // --- Student without class ---

    public function test_student_with_no_class_cannot_list_exams(): void
    {
        $student = $this->createStudent();

        $response = $this->actingAs($student)
            ->get('/student/exams');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->where('noClass', true));
    }

    // --- Lecturer access ---

    public function test_lecturer_can_access_exams_for_their_subjects(): void
    {
        $lecturer = $this->createLecturer();
        $subject = Subject::factory()->create(['created_by' => $lecturer->id]);
        $exam = Exam::factory()->create([
            'created_by' => $lecturer->id,
            'subject_id' => $subject->id,
        ]);

        $response = $this->actingAs($lecturer)
            ->get("/lecturer/exams/{$exam->id}");

        $response->assertStatus(200);
    }
}
