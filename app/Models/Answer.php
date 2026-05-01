<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Answer extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_session_id',
        'question_id',
        'type',
        'selected_option_id',
        'text_answer',
        'score',
        'reviewer_comment',
    ];

    protected function casts(): array
    {
        return ['score' => 'float'];
    }

    public function newFromBuilder($attributes = [], $connection = null): static
    {
        $attributes = (array) $attributes;
        $type = $attributes['type'] ?? null;

        $model = match ($type) {
            'mcq' => new McqAnswer,
            'open_text' => new OpenTextAnswer,
            default => new static,
        };

        $model->exists = true;
        $model->setRawAttributes((array) $attributes, true);
        $model->setConnection($connection ?? $this->getConnectionName());
        $model->fireModelEvent('retrieved', false);

        return $model;
    }

    public function examSession(): BelongsTo
    {
        return $this->belongsTo(ExamSession::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function selectedOption(): BelongsTo
    {
        return $this->belongsTo(Option::class, 'selected_option_id');
    }

    public function assignScore(float $score): void
    {
        $this->question->validateScore($score);
        $this->score = $score;
        $this->save();

        AuditLog::record('answer.scored', $this, Auth::user());
    }

    /** Delegate to subclass — base no-op so MCQ answers don't error */
    public function autoGrade(): void {}
}
