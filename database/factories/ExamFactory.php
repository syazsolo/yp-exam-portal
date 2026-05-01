<?php

namespace Database\Factories;

use App\Models\Exam;
use App\Models\Option;
use App\Models\Question;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Exam> */
class ExamFactory extends Factory
{
    public function definition(): array
    {
        $start = now()->addDay();
        $end = $start->copy()->addHours(2);

        return [
            'title' => fake()->sentence(4),
            'subject_id' => Subject::factory(),
            'created_by' => User::factory()->lecturer(),
            'time_limit_minutes' => fake()->randomElement([15, 20, 30, 45, 60, 90]),
            'default_question_weight' => 1.0,
            'status' => 'draft',
            'starts_at' => $start,
            'ends_at' => $end,
        ];
    }

    public function active(): static
    {
        return $this->state(['status' => 'active']);
    }

    public function closed(): static
    {
        return $this->state(['status' => 'closed']);
    }

    public function draft(): static
    {
        return $this->state(['status' => 'draft']);
    }

    /** Exam with only MCQ questions (2 questions, each with 4 options). */
    public function mcqOnly(): static
    {
        return $this->afterCreating(function (Exam $exam) {
            Question::factory()->mcq()->count(2)->create(['exam_id' => $exam->id])
                ->each(function (Question $question) {
                    Option::factory()->correct()->create(['question_id' => $question->id]);
                    Option::factory()->incorrect()->count(3)->create(['question_id' => $question->id]);
                });
        });
    }

    /** Exam with at least one open-text question (plus one MCQ). */
    public function withOpenText(): static
    {
        return $this->afterCreating(function (Exam $exam) {
            $mcq = Question::factory()->mcq()->create(['exam_id' => $exam->id]);
            Option::factory()->correct()->create(['question_id' => $mcq->id]);
            Option::factory()->incorrect()->count(3)->create(['question_id' => $mcq->id]);

            Question::factory()->openText()->create(['exam_id' => $exam->id]);
        });
    }
}
