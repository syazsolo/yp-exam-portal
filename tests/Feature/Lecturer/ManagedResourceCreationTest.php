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

    public function test_lecturer_cannot_create_class(): void
    {
        $lecturer = $this->createLecturer();

        $response = $this->actingAs($lecturer)
            ->post('/lecturer/classes', [
                'name' => 'Class A1',
                'subject_ids' => [],
            ]);

        $this->assertContains($response->getStatusCode(), [404, 405]);
        $this->assertDatabaseMissing('school_classes', [
            'name' => 'Class A1',
            'created_by' => $lecturer->id,
        ]);
    }
}
