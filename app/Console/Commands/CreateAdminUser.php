<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CreateAdminUser extends Command
{
    protected $signature = 'admin:create
        {--email= : Email address for the admin account}
        {--name= : Display name for the admin account}';

    protected $description = 'Create an admin user without exposing public admin registration';

    public function handle(): int
    {
        $name = $this->option('name') ?? $this->ask('Name');
        $email = strtolower($this->option('email') ?: $this->ask('Email'));
        $password = $this->secret('Password');
        $passwordConfirmation = $this->secret('Confirm password');

        $validator = Validator::make([
            'email' => $email,
            'name' => $name,
            'password' => $password,
            'password_confirmation' => $passwordConfirmation,
        ], [
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $attributes = [
            'name' => $name,
            'email' => $email,
            'role' => UserRole::Admin,
            'password' => Hash::make($password),
            'email_verified_at' => now(),
        ];

        $user = new User;
        $user->forceFill($attributes)->save();
        $this->info("Created admin user {$user->email}.");

        return self::SUCCESS;
    }
}
