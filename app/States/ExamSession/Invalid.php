<?php

namespace App\States\ExamSession;

class Invalid extends ExamSessionState
{
    public static string $name = 'invalid';

    public function label(): string
    {
        return 'Invalid';
    }
}
