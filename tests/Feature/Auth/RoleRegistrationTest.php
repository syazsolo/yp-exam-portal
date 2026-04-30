<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class RoleRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_provides_role_options(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Auth/Register')
            ->has('roles', 2)
            ->where('roles.0.value', 'lecturer')
            ->where('roles.1.value', 'student')
        );
    }

    public function test_user_can_register_as_lecturer(): void
    {
        $response = $this->post('/register', [
            'name' => 'Jane Lecturer',
            'email' => 'lecturer@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'lecturer',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));

        $user = User::where('email', 'lecturer@example.com')->first();
        $this->assertNotNull($user);
        $this->assertSame(UserRole::Lecturer, $user->role);
    }

    public function test_user_can_register_as_student(): void
    {
        $response = $this->post('/register', [
            'name' => 'John Student',
            'email' => 'student@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'student',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));

        $user = User::where('email', 'student@example.com')->first();
        $this->assertNotNull($user);
        $this->assertSame(UserRole::Student, $user->role);
    }

    public function test_registration_fails_without_role(): void
    {
        $response = $this->from('/register')->post('/register', [
            'name' => 'No Role',
            'email' => 'norole@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('role');
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'norole@example.com']);
    }

    public function test_registration_fails_with_invalid_role(): void
    {
        $response = $this->from('/register')->post('/register', [
            'name' => 'Bad Role',
            'email' => 'badrole@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'admin',
        ]);

        $response->assertSessionHasErrors('role');
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'badrole@example.com']);
    }
}
