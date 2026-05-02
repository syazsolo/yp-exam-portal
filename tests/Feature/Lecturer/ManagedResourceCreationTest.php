<?php

namespace Tests\Feature\Lecturer;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManagedResourceCreationTest extends TestCase
{
    use RefreshDatabase;

    // TODO - maybe should provide an ID
    public function test_lecturer_can_create_subject_without_providing_id(): void
    {
        $lecturer = $this->createLecturer();

        $response = $this->actingAs($lecturer)
            ->post(route('lecturer.subjects.store'), [
                'name' => 'Software Fundamentals',
                'description' => 'Introductory programming concepts.',
            ]);

        $response->assertRedirect(route('lecturer.subjects.index'));
        $this->assertDatabaseHas('subjects', [
            'name' => 'Software Fundamentals',
            'created_by' => $lecturer->id,
        ]);

        $this->assertNotNull(
            $lecturer->createdSubjects()->where('name', 'Software Fundamentals')->value('id')
        );
    }

    // TODO - maybe should provide an ID
    public function test_lecturer_can_create_class_without_providing_id(): void
    {
        $lecturer = $this->createLecturer();

        $response = $this->actingAs($lecturer)
            ->post(route('lecturer.classes.store'), [
                'name' => 'Class A1',
                'subject_ids' => [],
            ]);

        $response->assertRedirect(route('lecturer.classes.index'));
        $this->assertDatabaseHas('school_classes', [
            'name' => 'Class A1',
            'created_by' => $lecturer->id,
        ]);

        $this->assertNotNull(
            $lecturer->createdClasses()->where('name', 'Class A1')->value('id')
        );
    }
}
