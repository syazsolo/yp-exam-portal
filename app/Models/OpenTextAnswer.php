<?php

namespace App\Models;

class OpenTextAnswer extends Answer
{
    protected $table = 'answers';

    protected static function booted(): void
    {
        static::creating(function (self $answer) {
            $answer->type = 'open_text';
        });
    }

    public function isReviewed(): bool
    {
        return $this->score !== null;
    }
}
