<?php

namespace Database\Factories;

use App\Models\Option;
use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Option> */
class OptionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'question_id' => Question::factory(),
            'body' => fake()->sentence(),
            'is_correct' => false,
            'order' => 0,
        ];
    }

    public function correct(): static
    {
        return $this->state(['is_correct' => true]);
    }

    public function incorrect(): static
    {
        return $this->state(['is_correct' => false]);
    }
}
