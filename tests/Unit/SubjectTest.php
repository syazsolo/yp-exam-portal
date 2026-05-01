<?php

namespace Tests\Unit;

use App\Models\SchoolClass;
use App\Models\Subject;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SubjectTest extends TestCase
{
    use RefreshDatabase;

    // --- ID shape ---

    public function test_subjects_table_has_string_id(): void
    {
        $type = Schema::getColumnType('subjects', 'id');
        $this->assertContains($type, ['string', 'varchar', 'char', 'text']);
    }

    public function test_can_create_subject_with_pretty_text_id(): void
    {
        $subject = Subject::factory()->create(['id' => 'ECP3086']);

        $this->assertSame('ECP3086', $subject->fresh()->id);
    }

    public function test_subject_id_must_be_unique(): void
    {
        Subject::factory()->create(['id' => 'ECP3086']);

        $this->expectException(QueryException::class);
        Subject::factory()->create(['id' => 'ECP3086']);
    }

    // --- Relationships ---

    public function test_class_can_have_many_subjects(): void
    {
        $class = SchoolClass::factory()->create();
        $subjects = Subject::factory()->count(3)->create();

        $class->subjects()->attach($subjects->pluck('id'));

        $this->assertCount(3, $class->subjects);
    }

    public function test_subject_can_belong_to_many_classes(): void
    {
        $subject = Subject::factory()->create();
        $classes = SchoolClass::factory()->count(2)->create();

        $subject->classes()->attach($classes->pluck('id'));

        $this->assertCount(2, $subject->classes);
    }

    // --- Soft delete ---

    public function test_soft_delete_excludes_subject_from_active_queries(): void
    {
        $subject = Subject::factory()->create();
        $subject->delete();

        $this->assertNull(Subject::find($subject->id));
    }

    public function test_soft_deleted_subject_is_retained_in_database(): void
    {
        $subject = Subject::factory()->create();
        $subject->delete();

        $this->assertSoftDeleted($subject);
    }
}
