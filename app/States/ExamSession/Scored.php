<?php

namespace App\States\ExamSession;

class Scored extends ExamSessionState
{
    public static string $name = 'scored';

    public function label(): string
    {
        return 'Scored';
    }
}
