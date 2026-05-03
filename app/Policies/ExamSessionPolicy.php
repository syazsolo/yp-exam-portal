<?php

namespace App\Policies;

use App\Models\ExamSession;
use App\Models\User;

class ExamSessionPolicy
{
    public function review(User $user, ExamSession $session): bool
    {
        return $session->exam->created_by === $user->id;
    }

    public function finalize(User $user, ExamSession $session): bool
    {
        return $session->exam->created_by === $user->id;
    }
}
