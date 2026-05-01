<?php

namespace App\States\ExamSession;

class PendingReview extends ExamSessionState
{
    public static string $name = 'pending_review';

    public function label(): string
    {
        return 'Pending Review';
    }
}
