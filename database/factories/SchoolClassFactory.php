<?php

namespace Database\Factories;

use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<SchoolClass> */
class SchoolClassFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => strtoupper(fake()->unique()->bothify('??###?')),
            'name' => 'Class '.fake()->randomElement(['A', 'B', 'C', 'D', 'E', 'F']).fake()->numberBetween(1, 9),
            'created_by' => User::factory()->lecturer(),
        ];
    }
}
