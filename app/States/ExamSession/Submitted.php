<?php

namespace App\States\ExamSession;

class Submitted extends ExamSessionState
{
    public static string $name = 'submitted';

    public function label(): string
    {
        return 'Submitted';
    }
}
