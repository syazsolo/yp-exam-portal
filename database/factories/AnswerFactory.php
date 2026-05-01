<?php

namespace Database\Factories;

use App\Models\Answer;
use App\Models\ExamSession;
use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Answer> */
class AnswerFactory extends Factory
{
    protected $model = Answer::class;

    public function definition(): array
    {
        return [
            'exam_session_id' => ExamSession::factory(),
            'question_id' => Question::factory(),
            'type' => 'mcq',
            'score' => null,
        ];
    }

    public function mcq(): static
    {
        return $this->state(['type' => 'mcq']);
    }

    public function openText(): static
    {
        return $this->state(['type' => 'open_text', 'text_answer' => fake()->paragraph()]);
    }
}
