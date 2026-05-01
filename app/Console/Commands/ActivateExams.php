<?php

namespace App\Console\Commands;

use App\Models\Exam;
use Illuminate\Console\Command;

class ActivateExams extends Command
{
    protected $signature = 'exams:activate';

    protected $description = 'Activate exams whose starts_at has passed';

    public function handle(): void
    {
        $count = Exam::where('status', 'draft')
            ->whereNotNull('starts_at')
            ->where('starts_at', '<=', now())
            ->update(['status' => 'active']);

        $this->info("Activated {$count} exam(s).");
    }
}
