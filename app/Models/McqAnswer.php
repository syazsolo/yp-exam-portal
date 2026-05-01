<?php

namespace App\Models;

class McqAnswer extends Answer
{
    protected $table = 'answers';

    protected static function booted(): void
    {
        static::creating(function (self $answer) {
            $answer->type = 'mcq';
        });
    }

    public function autoGrade(): void
    {
        $correct = $this->selectedOption?->is_correct ?? false;
        $this->score = $correct ? $this->question->effectiveWeight() : 0.0;
        $this->save();
    }
}
