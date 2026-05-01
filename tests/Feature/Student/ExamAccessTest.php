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

    public function test_student_can_see_exam_when_subject_is_in_their_class(): void
    {
        $student = $this->createStudent();
        $class = SchoolClass::factory()->create();
        $subject = Subject::factory()->create();
        $exam = Exam::factory()->create(['subject_id' => $subject->id]);

        $student->classes()->attach($class->id, ['assigned_at' => now()]);
        $class->subjects()->attach($subject->id);

        $response = $this->actingAs($student)
            ->get("/student/exams/{$exam->id}");

        $response->assertStatus(200);
    }

    public function test_student_cannot_see_exam_when_subject_is_not_in_their_class(): void
    {
        $student = $this->createStudent();
        $class = SchoolClass::factory()->create();
        $otherSubject = Subject::factory()->create();
        $exam = Exam::factory()->create(['subject_id' => $otherSubject->id]);

        $student->classes()->attach($class->id, ['assigned_at' => now()]);
        // class has no subjects attached

        $response = $this->actingAs($student)
            ->get("/student/exams/{$exam->id}");

        $response->assertForbidden();
    }

    // --- Student without class ---

    public function test_student_with_no_class_cannot_access_any_exam(): void
    {
        $student = $this->createStudent();
        $exam = Exam::factory()->create();

        $response = $this->actingAs($student)
            ->get("/student/exams/{$exam->id}");

        $response->assertForbidden();
    }

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
        $subject = Subject::factory()->create();
        $exam = Exam::factory()->create(['subject_id' => $subject->id]);

        $response = $this->actingAs($lecturer)
            ->get("/lecturer/exams/{$exam->id}");

        $response->assertStatus(200);
    }
}
