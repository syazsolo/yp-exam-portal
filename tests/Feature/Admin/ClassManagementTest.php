<?php

namespace Tests\Feature\Admin;

use App\Models\SchoolClass;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
