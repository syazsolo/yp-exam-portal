<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminCreateCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_an_admin_user_from_prompts(): void
    {
        $this->artisan('admin:create')
            ->expectsQuestion('Name', 'Head Admin')
            ->expectsQuestion('Email', 'admin@example.com')
            ->expectsQuestion('Password', 'password')
            ->expectsQuestion('Confirm password', 'password')
            ->expectsOutput('Created admin user admin@example.com.')
            ->assertExitCode(Command::SUCCESS);

        $user = User::where('email', 'admin@example.com')->first();

        $this->assertNotNull($user);
        $this->assertSame('Head Admin', $user->name);
        $this->assertSame(UserRole::Admin, $user->role);
        $this->assertTrue(Hash::check('password', $user->password));
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_it_accepts_name_and_email_options(): void
    {
        $this->artisan('admin:create --email=head@example.com --name="Head Admin"')
            ->expectsQuestion('Password', 'password')
            ->expectsQuestion('Confirm password', 'password')
            ->expectsOutput('Created admin user head@example.com.')
            ->assertExitCode(Command::SUCCESS);

        $this->assertDatabaseHas('users', [
            'email' => 'head@example.com',
            'name' => 'Head Admin',
            'role' => UserRole::Admin->value,
        ]);
    }

    public function test_it_fails_when_name_is_missing(): void
    {
        $this->artisan('admin:create --email=admin@example.com --name=')
            ->expectsQuestion('Password', 'password')
            ->expectsQuestion('Confirm password', 'password')
            ->expectsOutput('The name field is required.')
            ->assertExitCode(Command::FAILURE);

        $this->assertDatabaseMissing('users', ['email' => 'admin@example.com']);
    }

    public function test_it_fails_when_email_is_invalid(): void
    {
        $this->artisan('admin:create --name="Head Admin" --email=not-an-email')
            ->expectsQuestion('Password', 'password')
            ->expectsQuestion('Confirm password', 'password')
            ->expectsOutput('The email field must be a valid email address.')
            ->assertExitCode(Command::FAILURE);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_it_fails_when_password_confirmation_does_not_match(): void
    {
        $this->artisan('admin:create --name="Head Admin" --email=admin@example.com')
            ->expectsQuestion('Password', 'password')
            ->expectsQuestion('Confirm password', 'different-password')
            ->expectsOutput('The password field confirmation does not match.')
            ->assertExitCode(Command::FAILURE);

        $this->assertDatabaseMissing('users', ['email' => 'admin@example.com']);
    }

    public function test_it_fails_when_password_does_not_meet_default_rules(): void
    {
        $this->artisan('admin:create --name="Head Admin" --email=admin@example.com')
            ->expectsQuestion('Password', 'short')
            ->expectsQuestion('Confirm password', 'short')
            ->expectsOutput('The password field must be at least 8 characters.')
            ->assertExitCode(Command::FAILURE);

        $this->assertDatabaseMissing('users', ['email' => 'admin@example.com']);
    }

    public function test_it_fails_when_user_with_email_already_exists(): void
    {
        User::factory()->student()->create(['email' => 'admin@example.com']);

        $this->artisan('admin:create --name="Head Admin" --email=admin@example.com')
            ->expectsQuestion('Password', 'password')
            ->expectsQuestion('Confirm password', 'password')
            ->expectsOutput('The email has already been taken.')
            ->assertExitCode(Command::FAILURE);

        $user = User::where('email', 'admin@example.com')->first();

        $this->assertSame(UserRole::Student, $user->role);
    }
}
