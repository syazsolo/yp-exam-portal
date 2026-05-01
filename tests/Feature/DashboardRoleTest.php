<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_lecturer_dashboard_exposes_lecturer_role(): void
    {
        $user = $this->createLecturer();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('auth.user.role', 'lecturer')
        );
    }

    public function test_student_dashboard_exposes_student_role(): void
    {
        $user = $this->createStudent();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('auth.user.role', 'student')
        );
    }
}
