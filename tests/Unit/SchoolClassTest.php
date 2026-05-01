<?php

namespace Tests\Unit;

use App\Models\SchoolClass;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SchoolClassTest extends TestCase
{
    use RefreshDatabase;

    // --- ID shape ---

    public function test_school_classes_table_has_string_id(): void
    {
        // Laravel 11 returns native DB type names ("varchar"/"char") instead of
        // the abstract "string" alias used in earlier versions. Either is fine
        // — what matters is that the id is a textual column, not numeric.
        $type = Schema::getColumnType('school_classes', 'id');
        $this->assertContains($type, ['string', 'varchar', 'char', 'text']);
    }

    public function test_can_create_class_with_pretty_text_id(): void
    {
        $class = SchoolClass::factory()->create(['id' => 'CS2025A']);

        $this->assertSame('CS2025A', $class->fresh()->id);
    }

    public function test_class_id_must_be_unique(): void
    {
        SchoolClass::factory()->create(['id' => 'CS2025A']);

        $this->expectException(QueryException::class);
        SchoolClass::factory()->create(['id' => 'CS2025A']);
    }

    // --- Soft delete ---

    public function test_soft_delete_excludes_class_from_active_queries(): void
    {
        $class = SchoolClass::factory()->create();
        $class->delete();

        $this->assertNull(SchoolClass::find($class->id));
    }

    public function test_soft_deleted_class_is_retained_in_database(): void
    {
        $class = SchoolClass::factory()->create();
        $class->delete();

        $this->assertSoftDeleted($class);
    }

    public function test_soft_deleted_class_still_appears_in_student_class_history(): void
    {
        $student = $this->createStudent();
        $class = SchoolClass::factory()->create();
        $student->classes()->attach($class->id, ['assigned_at' => now()]);

        $class->delete();

        // classHistory uses withTrashed so past enrollment is still visible
        $this->assertCount(1, $student->classHistory()->get());
    }
}
