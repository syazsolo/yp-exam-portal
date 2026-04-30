<?php

namespace Tests\Unit;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class UserRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_table_has_role_column(): void
    {
        $this->assertTrue(Schema::hasColumn('users', 'role'));
    }

    public function test_role_is_fillable(): void
    {
        $user = new User;
        $this->assertContains('role', $user->getFillable());
    }

    public function test_can_create_lecturer_user(): void
    {
        $user = User::factory()->create(['role' => UserRole::Lecturer]);
        $this->assertSame(UserRole::Lecturer, $user->fresh()->role);
    }

    public function test_can_create_student_user(): void
    {
        $user = User::factory()->create(['role' => UserRole::Student]);
        $this->assertSame(UserRole::Student, $user->fresh()->role);
    }

    public function test_factory_requires_explicit_role(): void
    {
        $this->expectException(QueryException::class);
        User::factory()->create();
    }

    public function test_role_cannot_be_null(): void
    {
        $this->expectException(QueryException::class);
        User::factory()->create(['role' => null]);
    }

    public function test_factory_lecturer_state(): void
    {
        $user = User::factory()->lecturer()->create();
        $this->assertSame(UserRole::Lecturer, $user->role);
    }

    public function test_factory_student_state(): void
    {
        $user = User::factory()->student()->create();
        $this->assertSame(UserRole::Student, $user->role);
    }
}
