<?php

namespace Database\Factories;

use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Subject> */
class SubjectFactory extends Factory
{
    public function definition(): array
    {
        $prefixes = ['ECP', 'MAT', 'PHY', 'CHE', 'BIO', 'CSC', 'ENG', 'HIS'];

        return [
            'id' => fake()->unique()->randomElement($prefixes).fake()->unique()->numberBetween(1000, 9999),
            'name' => fake()->randomElement(['Mathematics', 'Physics', 'English', 'History', 'Computer Science', 'Chemistry', 'Biology']),
            'description' => fake()->sentence(),
            'created_by' => User::factory()->lecturer(),
        ];
    }
}
