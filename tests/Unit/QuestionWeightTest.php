<?php

namespace Tests\Unit;

use App\Models\Exam;
use App\Models\Option;
use App\Models\Question;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuestionWeightTest extends TestCase
{
    use RefreshDatabase;

    // --- Weight configuration ---

    public function test_question_can_have_a_custom_weight(): void
    {
        $question = Question::factory()->create(['weight' => 5.0]);

        $this->assertSame(5.0, (float) $question->fresh()->weight);
    }

    public function test_question_weight_cannot_be_zero(): void
    {
        $this->expectException(QueryException::class);
        Question::factory()->create(['weight' => 0]);
    }

    public function test_question_weight_cannot_be_negative(): void
    {
        $this->expectException(QueryException::class);
        Question::factory()->create(['weight' => -1]);
    }

    public function test_question_inherits_exam_default_weight_when_not_overridden(): void
    {
        $exam = Exam::factory()->create(['default_question_weight' => 3.0]);
        $question = Question::factory()->create(['exam_id' => $exam->id, 'weight' => null]);

        $this->assertSame(3.0, (float) $question->fresh()->effectiveWeight());
    }

    public function test_question_custom_weight_overrides_exam_default(): void
    {
        $exam = Exam::factory()->create(['default_question_weight' => 3.0]);
        $question = Question::factory()->create(['exam_id' => $exam->id, 'weight' => 7.0]);

        $this->assertSame(7.0, (float) $question->fresh()->effectiveWeight());
    }

    // --- MCQ scoring ---

    public function test_mcq_correct_answer_scores_full_weight(): void
    {
        $question = Question::factory()->mcq()->create(['weight' => 4.0]);
        $correct = Option::factory()->correct()->create(['question_id' => $question->id]);

        $score = $question->scoreAnswer($correct->id);

        $this->assertSame(4.0, (float) $score);
    }

    public function test_mcq_wrong_answer_scores_zero(): void
    {
        $question = Question::factory()->mcq()->create(['weight' => 4.0]);
        $wrong = Option::factory()->incorrect()->create(['question_id' => $question->id]);

        $score = $question->scoreAnswer($wrong->id);

        $this->assertSame(0.0, (float) $score);
    }

    // --- Open-text score constraints ---

    public function test_open_text_score_cannot_exceed_weight(): void
    {
        $question = Question::factory()->openText()->create(['weight' => 5.0]);

        $this->expectException(\InvalidArgumentException::class);
        $question->validateScore(6.0);
    }

    public function test_open_text_score_cannot_be_negative(): void
    {
        $question = Question::factory()->openText()->create(['weight' => 5.0]);

        $this->expectException(\InvalidArgumentException::class);
        $question->validateScore(-1.0);
    }

    public function test_open_text_score_at_exact_weight_is_valid(): void
    {
        $question = Question::factory()->openText()->create(['weight' => 5.0]);

        // should not throw
        $question->validateScore(5.0);

        $this->assertTrue(true);
    }

    public function test_open_text_score_of_zero_is_valid(): void
    {
        $question = Question::factory()->openText()->create(['weight' => 5.0]);

        // should not throw
        $question->validateScore(0.0);

        $this->assertTrue(true);
    }
}
