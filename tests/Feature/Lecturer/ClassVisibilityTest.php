<?php

namespace Tests\Feature\Lecturer;

use App\Models\SchoolClass;
use App\Models\Subject;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ClassVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_lecturer_sees_classes_that_include_their_subjects(): void
    {
        $admin = $this->createAdmin();
        $lecturer = $this->createLecturer();
        $subject = Subject::factory()->create(['created_by' => $lecturer->id]);
        $class = SchoolClass::factory()->create(['created_by' => $admin->id]);
        $class->subjects()->attach($subject->id);

        $response = $this->actingAs($lecturer)
            ->get(route('lecturer.classes.index'));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Lecturer/Classes/Index')
            ->has('classes', 1)
            ->where('classes.0.id', $class->id)
            ->where('classes.0.name', $class->name));
    }

    public function test_lecturer_does_not_see_classes_without_their_subjects(): void
    {
        $admin = $this->createAdmin();
        $lecturer = $this->createLecturer();
        $otherLecturer = $this->createLecturer();
        $subject = Subject::factory()->create(['created_by' => $otherLecturer->id]);
        $class = SchoolClass::factory()->create(['created_by' => $admin->id]);
        $class->subjects()->attach($subject->id);

        $response = $this->actingAs($lecturer)
            ->get(route('lecturer.classes.index'));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Lecturer/Classes/Index')
            ->has('classes', 0));
    }

    public function test_lecturer_can_view_roster_for_class_containing_their_subject(): void
    {
        $admin = $this->createAdmin();
        $lecturer = $this->createLecturer();
        $student = $this->createStudent();
        $subject = Subject::factory()->create(['created_by' => $lecturer->id]);
        $class = SchoolClass::factory()->create(['created_by' => $admin->id]);
        $class->subjects()->attach($subject->id);
        $student->classes()->attach($class->id, ['assigned_at' => now()]);

        $response = $this->actingAs($lecturer)
            ->get(route('lecturer.classes.show', $class));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Lecturer/Classes/Show')
            ->where('schoolClass.id', $class->id)
            ->where('schoolClass.students.0.id', $student->id));
    }

    public function test_lecturer_cannot_add_student_to_class(): void
    {
        $admin = $this->createAdmin();
        $lecturer = $this->createLecturer();
        $student = $this->createStudent();
        $subject = Subject::factory()->create(['created_by' => $lecturer->id]);
        $class = SchoolClass::factory()->create(['created_by' => $admin->id]);
        $class->subjects()->attach($subject->id);

        $response = $this->actingAs($lecturer)
            ->post("/lecturer/classes/{$class->id}/students", [
                'email' => $student->email,
            ]);

        $this->assertContains($response->getStatusCode(), [404, 405]);
        $this->assertDatabaseMissing('class_user', [
            'class_id' => $class->id,
            'user_id' => $student->id,
        ]);
    }

    public function test_lecturer_cannot_remove_student_from_class(): void
    {
        $admin = $this->createAdmin();
        $lecturer = $this->createLecturer();
        $student = $this->createStudent();
        $subject = Subject::factory()->create(['created_by' => $lecturer->id]);
        $class = SchoolClass::factory()->create(['created_by' => $admin->id]);
        $class->subjects()->attach($subject->id);
        $student->classes()->attach($class->id, ['assigned_at' => now()]);

        $response = $this->actingAs($lecturer)
            ->delete("/lecturer/classes/{$class->id}/students/{$student->id}");

        $this->assertContains($response->getStatusCode(), [404, 405]);
        $this->assertDatabaseHas('class_user', [
            'class_id' => $class->id,
            'user_id' => $student->id,
            'unassigned_at' => null,
        ]);
    }

    public function test_lecturer_class_page_does_not_expose_add_student_control(): void
    {
        $source = file_get_contents(resource_path('js/Pages/Lecturer/Classes/Show.vue'));

        $this->assertStringNotContainsString('lecturer.classes.students.add', $source);
        $this->assertStringNotContainsString('lecturer.classes.students.remove', $source);
        $this->assertStringNotContainsString('Add Student by Email', $source);
    }
}
