<?php

namespace App\States\ExamSession;

class Pending extends ExamSessionState
{
    public static string $name = 'pending';

    public function label(): string
    {
        return 'Pending';
    }
}
