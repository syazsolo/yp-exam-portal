<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    use HasFactory;

    protected $fillable = ['exam_id', 'type', 'text', 'order', 'weight'];

    protected function casts(): array
    {
        return ['weight' => 'float'];
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(Option::class)->orderBy('order');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }

    public function scopeMcq($query)
    {
        return $query->where('type', 'mcq');
    }

    public function scopeOpenText($query)
    {
        return $query->where('type', 'open_text');
    }

    public function isMcq(): bool
    {
        return $this->type === 'mcq';
    }

    public function isOpenText(): bool
    {
        return $this->type === 'open_text';
    }

    public function effectiveWeight(): float
    {
        return (float) ($this->weight ?? $this->exam->default_question_weight);
    }

    public function scoreAnswer(int|string $optionId): float
    {
        $option = $this->options()->find($optionId);

        return ($option?->is_correct) ? $this->effectiveWeight() : 0.0;
    }

    public function validateScore(float $score): void
    {
        $max = $this->effectiveWeight();

        if ($score < 0 || $score > $max) {
            throw new \InvalidArgumentException(
                "Score {$score} is out of range [0, {$max}]."
            );
        }
    }
}
