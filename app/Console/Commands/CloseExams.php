<?php

namespace App\Console\Commands;

use App\Models\Exam;
use App\Models\ExamSession;
use App\States\ExamSession\Pending;
use Illuminate\Console\Command;

class CloseExams extends Command
{
    protected $signature = 'exams:close';

    protected $description = 'Auto-submit pending sessions and close exams whose ends_at has passed';

    public function handle(): void
    {
        $endedExams = Exam::whereNotNull('ends_at')
            ->where('ends_at', '<=', now())
            ->pluck('id');

        $sessions = ExamSession::whereIn('exam_id', $endedExams)
            ->whereState('state', Pending::class)
            ->with(['exam.questions', 'answers'])
            ->get();

        foreach ($sessions as $session) {
            $session->autoSubmit();
        }

        $closed = Exam::whereIn('id', $endedExams)
            ->where('status', 'active')
            ->update(['status' => 'closed']);

        $this->info("Auto-submitted {$sessions->count()} session(s). Closed {$closed} exam(s).");
    }
}
