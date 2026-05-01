<?php

namespace Database\Factories;

use App\Models\Exam;
use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Question> */
class QuestionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'exam_id' => Exam::factory(),
            'type' => 'mcq',
            'text' => fake()->sentence().'?',
            'order' => 0,
            'weight' => 1.0,
        ];
    }

    public function mcq(): static
    {
        return $this->state(['type' => 'mcq']);
    }

    public function openText(): static
    {
        // Default weight high enough so reviewer-assigned scores like 3.0 fit within range.
        return $this->state(['type' => 'open_text', 'weight' => 10.0]);
    }
}
