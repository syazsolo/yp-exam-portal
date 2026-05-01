<?php

namespace App\Enums;

enum QuestionType: string
{
    case Mcq = 'mcq';
    case OpenText = 'open_text';
}
