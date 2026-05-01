<?php

namespace Database\Factories;

use App\Models\Answer;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\Question;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ExamSession> */
class ExamSessionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'exam_id' => Exam::factory(),
            'user_id' => User::factory()->student(),
            'state' => 'pending',
            'started_at' => now(),
        ];
    }

    public function pending(): static
    {
        return $this->state(['state' => 'pending']);
    }

    public function pendingReview(): static
    {
        return $this->for(Exam::factory()->withOpenText())
            ->state([
                'state' => 'pending_review',
                'submitted_at' => now(),
            ])
            ->afterCreating(function (ExamSession $session) {
                $this->seedAnswers($session);
            });
    }

    public function scored(): static
    {
        return $this->state([
            'state' => 'scored',
            'submitted_at' => now(),
            'score_raw' => 8.0,
            'score_max' => 10.0,
        ]);
    }

    public function withAnswers(): static
    {
        return $this->afterCreating(function (ExamSession $session) {
            $this->seedAnswers($session);
        });
    }

    private function seedAnswers(ExamSession $session): void
    {
        $session->exam->questions->each(function (Question $question) use ($session) {
            Answer::factory()->create([
                'exam_session_id' => $session->id,
                'question_id' => $question->id,
                'type' => $question->type,
            ]);
        });
    }
}
