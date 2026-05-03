<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamSession;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $student = $request->user();
        $activeClass = $student->activeClass();
        $noClass = $activeClass === null;

        $subjectIds = $noClass
            ? collect()
            : $activeClass->subjects->pluck('id');

        $availableExams = $noClass ? collect() : Exam::with('subject')
            ->whereIn('subject_id', $subjectIds)
            ->where('status', 'active')
            ->get()
            ->map(function (Exam $exam) use ($student) {
                $session = $exam->sessions()
                    ->where('user_id', $student->id)
                    ->latest()
                    ->first();

                return [
                    'id' => $exam->id,
                    'title' => $exam->title,
                    'subject' => $exam->subject->name,
                    'time_limit' => $exam->time_limit_minutes,
                    'ends_at' => $exam->ends_at,
                    'attempt_state' => $session ? (string) $session->state : null,
                    'session_id' => $session?->id,
                ];
            });

        $mySessions = ExamSession::with(['exam.subject'])
            ->where('user_id', $student->id)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'exam_title' => $s->exam->title,
                'subject' => $s->exam->subject->name,
                'state' => (string) $s->state,
                'score_label' => $s->scoreLabel(),
                'submitted_at' => $s->submitted_at,
            ]);

        return Inertia::render('Student/Dashboard', compact('availableExams', 'mySessions', 'noClass'));
    }
}
