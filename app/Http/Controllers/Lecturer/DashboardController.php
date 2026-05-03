<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\SchoolClass;
use App\Models\User;
use App\States\ExamSession\PendingReview;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $lecturer = $request->user();
        $classIds = SchoolClass::whereHas('subjects', fn ($query) => $query->where('subjects.created_by', $lecturer->id))
            ->pluck('id');

        $stats = [
            'active_exams' => Exam::where('created_by', $lecturer->id)->where('status', 'active')->count(),
            'total_exams' => Exam::where('created_by', $lecturer->id)->count(),
            'classes' => $classIds->count(),
            'students' => User::whereHas('classes', fn ($q) => $q
                ->whereIn('school_classes.id', $classIds)
                ->whereNull('class_user.unassigned_at'))->count(),
        ];

        $recentExams = Exam::with(['subject', 'sessions'])
            ->where('created_by', $lecturer->id)
            ->orderByDesc('created_at')
            ->take(5)
            ->get()
            ->map(fn ($exam) => [
                'id' => $exam->id,
                'title' => $exam->title,
                'subject' => $exam->subject->name,
                'status' => $exam->status,
                'time_limit' => $exam->time_limit_mins,
                'sessions_count' => $exam->sessions->count(),
            ]);

        $classes = SchoolClass::with(['subjects', 'students'])
            ->whereKey($classIds)
            ->orderBy('name')
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'students' => $c->students->count(),
                'subjects' => $c->subjects->pluck('name'),
            ]);

        $pendingReviews = ExamSession::whereState('state', PendingReview::class)
            ->whereHas('exam', fn ($q) => $q->where('created_by', $lecturer->id))
            ->count();

        return Inertia::render('Lecturer/Dashboard', compact('stats', 'recentExams', 'classes', 'pendingReviews'));
    }
}
