<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\States\ExamSession\ExamSessionState;
use App\States\ExamSession\Invalid;
use App\States\ExamSession\PendingReview;
use App\States\ExamSession\Scored;
use App\States\ExamSession\Submitted;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\ModelStates\HasStates;

class ExamSession extends Model
{
    use Auditable, HasFactory, HasStates;

    protected $fillable = [
        'exam_id', 'user_id', 'state',
        'started_at', 'submitted_at',
        'score_raw', 'score_max',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'submitted_at' => 'datetime',
            'state' => ExamSessionState::class,
        ];
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }

    public function mcqAnswers(): HasMany
    {
        return $this->hasMany(Answer::class)->where('type', 'mcq');
    }

    public function openTextAnswers(): HasMany
    {
        return $this->hasMany(Answer::class)->where('type', 'open_text');
    }

    /** Called by student. Validates time window; marks Invalid if expired. */
    public function submit(): void
    {
        $exam = $this->exam;

        if ($exam->ends_at && now()->isAfter($exam->ends_at)) {
            $this->state->transitionTo(Invalid::class);

            return;
        }

        if ($exam->time_limit_minutes && $this->started_at) {
            $deadline = $this->started_at->copy()->addMinutes($exam->time_limit_minutes);
            if (now()->isAfter($deadline)) {
                $this->state->transitionTo(Invalid::class);

                return;
            }
        }

        $this->autoSubmit();
    }

    /** Called by cron. Skips time-window checks; always does the normal submit flow. */
    public function autoSubmit(): void
    {
        $exam = $this->exam;

        $this->state->transitionTo(Submitted::class);
        $this->submitted_at = now();
        $this->save();

        $this->mcqAnswers()->with('question.options')->get()
            ->each(fn (Answer $a) => $a->autoGrade());

        if ($exam->hasOpenTextQuestions()) {
            $this->state->transitionTo(PendingReview::class);
        } else {
            $this->computeScores();
            $this->state->transitionTo(Scored::class);
        }

        $this->recordAudit('session.submitted');
    }

    public function markAllReviewed(): void
    {
        $this->computeScores();
        $this->state->transitionTo(Scored::class);
    }

    public function allOpenTextReviewed(): bool
    {
        return $this->openTextAnswers()->whereNull('score')->doesntExist();
    }

    public function computeScores(): void
    {
        $this->score_raw = (float) $this->answers()->sum('score');
        $this->score_max = (float) $this->exam->questions()->get()
            ->sum(fn (Question $q) => $q->effectiveWeight());
        $this->save();
    }

    public function scoreLabel(): string
    {
        if ($this->score_raw === null) {
            return '—';
        }

        return "{$this->score_raw}/{$this->score_max}";
    }

    public function scorePercent(): ?int
    {
        if (! $this->score_max) {
            return null;
        }

        return (int) round(($this->score_raw / $this->score_max) * 100);
    }

    private function recordAudit(string $event): void
    {
        AuditLog::record($event, $this);
    }
}
