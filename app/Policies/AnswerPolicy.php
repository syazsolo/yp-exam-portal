<?php

namespace App\Policies;

use App\Models\Answer;
use App\Models\User;

class AnswerPolicy
{
    public function score(User $user, Answer $answer): bool
    {
        $answer->loadMissing(['examSession.exam', 'question']);

        return $answer->exam_session_id !== null
            && $answer->question_id !== null
            && $answer->examSession->exam_id === $answer->question->exam_id
            && $answer->examSession->exam->created_by === $user->id;
    }
}
