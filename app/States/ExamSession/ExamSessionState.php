<?php

namespace App\States\ExamSession;

use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

abstract class ExamSessionState extends State
{
    abstract public function label(): string;

    public static function config(): StateConfig
    {
        return parent::config()
            ->default(Pending::class)
            ->allowTransition(Pending::class, Submitted::class)
            ->allowTransition(Pending::class, Invalid::class)
            ->allowTransition(Submitted::class, Scored::class)
            ->allowTransition(Submitted::class, PendingReview::class)
            ->allowTransition(PendingReview::class, Scored::class);
    }
}
