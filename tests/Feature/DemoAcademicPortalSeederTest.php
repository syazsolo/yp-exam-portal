<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Exam;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Database\Seeders\DemoAcademicPortalSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DemoAcademicPortalSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_seeder_uses_predictable_role_accounts(): void
    {
        $this->seed(DemoAcademicPortalSeeder::class);

        $this->assertSame(1, User::where('role', UserRole::Admin)->count());

        foreach (range(1, 3) as $number) {
            $lecturer = User::where('email', "lecturer{$number}@gmail.com")->first();

            $this->assertNotNull($lecturer);
            $this->assertTrue($lecturer->isLecturer());
            $this->assertTrue(Hash::check('qwertyuiop', $lecturer->password));
        }

        foreach (range(1, 15) as $number) {
            $student = User::where('email', "student{$number}@gmail.com")->first();

            $this->assertNotNull($student);
            $this->assertTrue($student->isStudent());
            $this->assertTrue(Hash::check('qwertyuiop', $student->password));
        }
    }

    public function test_demo_seeder_creates_classes_with_subjects_from_multiple_lecturers(): void
    {
        $this->seed(DemoAcademicPortalSeeder::class);

        $mixedClassCount = DB::table('class_subject')
            ->join('subjects', 'class_subject.subject_id', '=', 'subjects.id')
            ->select('class_subject.class_id')
            ->groupBy('class_subject.class_id')
            ->havingRaw('COUNT(DISTINCT subjects.created_by) > 1')
            ->get()
            ->count();

        $this->assertGreaterThanOrEqual(3, $mixedClassCount);
    }

    public function test_demo_seeder_enrolls_students_with_one_active_class_and_assigned_at(): void
    {
        $this->seed(DemoAcademicPortalSeeder::class);

        User::where('role', UserRole::Student)->each(function (User $student) {
            $activeEnrollments = DB::table('class_user')
                ->where('user_id', $student->id)
                ->whereNull('unassigned_at')
                ->get();

            $this->assertCount(1, $activeEnrollments);
            $this->assertNotNull($activeEnrollments->first()->assigned_at);
        });
    }

    public function test_demo_seeder_has_richer_demo_volume(): void
    {
        $this->seed(DemoAcademicPortalSeeder::class);

        $this->assertGreaterThanOrEqual(5, SchoolClass::count());
        $this->assertGreaterThanOrEqual(18, Subject::count());
        $this->assertGreaterThanOrEqual(10, Exam::count());
    }
}
