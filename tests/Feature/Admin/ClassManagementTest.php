<?php

namespace Tests\Feature\Admin;

use App\Models\SchoolClass;
use App\Models\Subject;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ClassManagementTest extends TestCase
{
    use RefreshDatabase;

    // --- Assign student to class ---

    public function test_admin_can_assign_student_to_a_class(): void
    {
        $admin = $this->createAdmin();
        $student = $this->createStudent();
        $class = SchoolClass::factory()->create();

        $response = $this->actingAs($admin)
            ->post("/admin/students/{$student->id}/enroll", [
                'class_id' => $class->id,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('class_user', [
            'user_id' => $student->id,
            'class_id' => $class->id,
        ]);

        $this->assertNotNull(
            DB::table('class_user')
                ->where('user_id', $student->id)
                ->where('class_id', $class->id)
                ->value('assigned_at')
        );
    }

    public function test_admin_dashboard_exposes_class_management_data(): void
    {
        $admin = $this->createAdmin();
        $lecturer = $this->createLecturer();
        $student = $this->createStudent();
        $subject = Subject::factory()->create([
            'name' => 'Applied Mathematics',
            'created_by' => $lecturer->id,
        ]);
        $class = SchoolClass::factory()->create([
            'name' => 'Form 5 Science',
            'created_by' => $admin->id,
        ]);

        $class->subjects()->attach($subject->id);
        $student->classes()->attach($class->id, ['assigned_at' => now()]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Dashboard')
            ->has('classes', 1)
            ->where('classes.0.id', $class->id)
            ->where('classes.0.name', 'Form 5 Science')
            ->where('classes.0.subject_ids.0', $subject->id)
            ->where('classes.0.subjects.0.name', 'Applied Mathematics')
            ->where('classes.0.active_students_count', 1)
            ->has('subjects', 1)
            ->where('subjects.0.id', $subject->id)
            ->where('subjects.0.creator.name', $lecturer->name)
            ->has('students', 1)
            ->where('students.0.id', $student->id)
            ->where('students.0.active_class.id', $class->id)
        );
    }

    public function test_non_admin_cannot_assign_student_to_class(): void
    {
        $lecturer = $this->createLecturer();
        $student = $this->createStudent();
        $class = SchoolClass::factory()->create();

        $response = $this->actingAs($lecturer)
            ->post("/admin/students/{$student->id}/enroll", [
                'class_id' => $class->id,
            ]);

        $response->assertForbidden();
    }

    // --- Reassign student ---

    public function test_admin_can_reassign_student_to_a_different_class(): void
    {
        $admin = $this->createAdmin();
        $student = $this->createStudent();
        $oldClass = SchoolClass::factory()->create();
        $newClass = SchoolClass::factory()->create();

        // initial enrollment
        $student->classes()->attach($oldClass->id, ['assigned_at' => now()->subMonth()]);

        $this->actingAs($admin)
            ->post("/admin/students/{$student->id}/enroll", [
                'class_id' => $newClass->id,
            ]);

        // active class is now the new one
        $this->assertSame($newClass->id, $student->fresh()->activeClass?->id);
    }

    public function test_reassigning_student_preserves_class_history(): void
    {
        $admin = $this->createAdmin();
        $student = $this->createStudent();
        $oldClass = SchoolClass::factory()->create();
        $newClass = SchoolClass::factory()->create();

        $student->classes()->attach($oldClass->id, ['assigned_at' => now()->subMonth()]);

        $this->actingAs($admin)
            ->post("/admin/students/{$student->id}/enroll", [
                'class_id' => $newClass->id,
            ]);

        // both classes appear in history
        $this->assertCount(2, $student->classHistory()->get());
    }

    public function test_student_belongs_to_at_most_one_active_class(): void
    {
        $admin = $this->createAdmin();
        $student = $this->createStudent();
        $classA = SchoolClass::factory()->create();
        $classB = SchoolClass::factory()->create();

        $student->classes()->attach($classA->id, ['assigned_at' => now()->subMonth()]);

        $this->actingAs($admin)
            ->post("/admin/students/{$student->id}/enroll", [
                'class_id' => $classB->id,
            ]);

        $activeClasses = $student->classHistory()
            ->wherePivotNull('unassigned_at')
            ->get();

        $this->assertCount(1, $activeClasses);
    }

    // --- No class state ---

    public function test_student_with_no_class_sees_contact_admin_message(): void
    {
        $student = $this->createStudent();

        $response = $this->actingAs($student)->get('/student/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->where('noClass', true));
    }

    public function test_student_with_a_class_does_not_see_contact_admin_message(): void
    {
        $student = $this->createStudent();
        $class = SchoolClass::factory()->create();
        $student->classes()->attach($class->id, ['assigned_at' => now()]);

        $response = $this->actingAs($student)->get('/student/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->where('noClass', false));
    }

    public function test_admin_can_create_class_with_subjects_from_multiple_lecturers(): void
    {
        $admin = $this->createAdmin();
        $lecturerA = $this->createLecturer();
        $lecturerB = $this->createLecturer();
        $subjectA = Subject::factory()->create(['created_by' => $lecturerA->id]);
        $subjectB = Subject::factory()->create(['created_by' => $lecturerB->id]);

        $response = $this->actingAs($admin)
            ->post(route('admin.classes.store'), [
                'id' => 'CLS-MIXED-A',
                'name' => 'Mixed Lecturer Class A',
                'subject_ids' => [$subjectA->id, $subjectB->id],
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('school_classes', [
            'id' => 'CLS-MIXED-A',
            'name' => 'Mixed Lecturer Class A',
            'created_by' => $admin->id,
        ]);
        $this->assertDatabaseHas('class_subject', [
            'class_id' => 'CLS-MIXED-A',
            'subject_id' => $subjectA->id,
        ]);
        $this->assertDatabaseHas('class_subject', [
            'class_id' => 'CLS-MIXED-A',
            'subject_id' => $subjectB->id,
        ]);
    }

    public function test_admin_can_sync_class_subjects_from_multiple_lecturers(): void
    {
        $admin = $this->createAdmin();
        $lecturerA = $this->createLecturer();
        $lecturerB = $this->createLecturer();
        $oldSubject = Subject::factory()->create(['created_by' => $lecturerA->id]);
        $newSubjectA = Subject::factory()->create(['created_by' => $lecturerA->id]);
        $newSubjectB = Subject::factory()->create(['created_by' => $lecturerB->id]);
        $class = SchoolClass::factory()->create(['created_by' => $admin->id]);
        $class->subjects()->attach($oldSubject->id);

        $response = $this->actingAs($admin)
            ->patch(route('admin.classes.update', $class), [
                'name' => 'Mixed Lecturer Class Updated',
                'subject_ids' => [$newSubjectA->id, $newSubjectB->id],
            ]);

        $response->assertRedirect();
        $this->assertSame('Mixed Lecturer Class Updated', $class->fresh()->name);
        $this->assertDatabaseMissing('class_subject', [
            'class_id' => $class->id,
            'subject_id' => $oldSubject->id,
        ]);
        $this->assertDatabaseHas('class_subject', [
            'class_id' => $class->id,
            'subject_id' => $newSubjectA->id,
        ]);
        $this->assertDatabaseHas('class_subject', [
            'class_id' => $class->id,
            'subject_id' => $newSubjectB->id,
        ]);
    }
}
