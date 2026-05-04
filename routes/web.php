<?php

use App\Enums\UserRole;
use App\Http\Controllers\Admin\ClassController as AdminClassController;
use App\Http\Controllers\Admin\EnrollmentController;
use App\Http\Controllers\Lecturer\ClassController;
use App\Http\Controllers\Lecturer\DashboardController as LecturerDashboard;
use App\Http\Controllers\Lecturer\ExamController;
use App\Http\Controllers\Lecturer\QuestionController;
use App\Http\Controllers\Lecturer\ReviewController;
use App\Http\Controllers\Lecturer\SubjectController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Student\DashboardController as StudentDashboard;
use App\Http\Controllers\Student\ExamSessionController;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route(Auth::user()->dashboardRoute());
    }

    return Inertia::render('Welcome');
})->name('home');

Route::get('/dashboard', function () {
    return redirect()->route(Auth::user()->dashboardRoute());
})->middleware(['auth', 'verified'])->name('dashboard');

// Admin routes
Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function () {
        $classes = SchoolClass::query()
            ->with(['subjects:id,name'])
            ->withCount([
                'students as active_students_count' => fn ($query) => $query->whereNull('class_user.unassigned_at'),
            ])
            ->orderBy('name')
            ->get()
            ->map(fn (SchoolClass $class) => [
                'id' => $class->id,
                'name' => $class->name,
                'subject_ids' => $class->subjects->pluck('id')->values(),
                'subjects' => $class->subjects
                    ->map(fn (Subject $subject) => [
                        'id' => $subject->id,
                        'name' => $subject->name,
                    ])
                    ->values(),
                'active_students_count' => $class->active_students_count,
            ])
            ->values();

        $subjects = Subject::query()
            ->with('creator:id,name')
            ->orderBy('name')
            ->get()
            ->map(fn (Subject $subject) => [
                'id' => $subject->id,
                'name' => $subject->name,
                'creator' => [
                    'id' => $subject->creator?->id,
                    'name' => $subject->creator?->name,
                ],
            ])
            ->values();

        $students = User::query()
            ->where('role', UserRole::Student->value)
            ->with([
                'classes' => fn ($query) => $query
                    ->whereNull('class_user.unassigned_at')
                    ->select('school_classes.id', 'school_classes.name'),
            ])
            ->orderBy('name')
            ->get()
            ->map(function (User $student) {
                $activeClass = $student->classes->first();

                return [
                    'id' => $student->id,
                    'name' => $student->name,
                    'email' => $student->email,
                    'active_class' => $activeClass
                        ? [
                            'id' => $activeClass->id,
                            'name' => $activeClass->name,
                        ]
                        : null,
                ];
            })
            ->values();

        return Inertia::render('Admin/Dashboard', [
            'classes' => $classes,
            'subjects' => $subjects,
            'students' => $students,
        ]);
    })->name('dashboard');

    Route::post('students/{student}/enroll', [EnrollmentController::class, 'enroll'])->name('students.enroll');
    Route::resource('classes', AdminClassController::class)->only(['store', 'update', 'destroy']);
});

// Lecturer routes
Route::middleware(['auth', 'verified', 'lecturer'])->prefix('lecturer')->name('lecturer.')->group(function () {
    Route::get('/', LecturerDashboard::class)->name('dashboard');

    Route::resource('subjects', SubjectController::class);
    Route::resource('classes', ClassController::class)->only(['index', 'show']);

    // Exams — nested under subject for creation, standalone for management
    Route::post('exams', [ExamController::class, 'store'])->name('exams.store');
    Route::post('subjects/{subject}/exams', [ExamController::class, 'store'])->name('subjects.exams.store');
    Route::resource('exams', ExamController::class)->except('store');

    // Questions nested under exam
    Route::get('exams/{exam}/questions/create', [QuestionController::class, 'create'])->name('exams.questions.create');
    Route::post('exams/{exam}/questions', [QuestionController::class, 'store'])->name('exams.questions.store');
    Route::resource('questions', QuestionController::class)->except('store', 'index', 'create');

    // Review / scoring
    Route::get('sessions/{session}/review', [ReviewController::class, 'show'])->name('sessions.review');
    Route::patch('answers/{answer}/score', [ReviewController::class, 'score'])->name('answers.score');
    Route::post('sessions/{session}/finalize', [ReviewController::class, 'finalize'])->name('sessions.finalize');
});

// Student routes
Route::middleware(['auth', 'verified', 'student'])->prefix('student')->name('student.')->group(function () {
    Route::get('/', StudentDashboard::class)->name('dashboard');
    Route::get('dashboard', StudentDashboard::class)->name('dashboard.alias');

    Route::get('exams', [ExamSessionController::class, 'index'])->name('exams.index');
    Route::post('exams/{exam}/start', [ExamSessionController::class, 'start'])->name('exams.start');

    Route::get('exam-sessions', [ExamSessionController::class, 'listSessions'])->name('exam-sessions.index');
    Route::get('exam-sessions/{session}', [ExamSessionController::class, 'showSession'])->name('exam-sessions.show');
    Route::post('exam-sessions/{session}/submit', [ExamSessionController::class, 'submit'])->name('exam-sessions.submit');
    Route::post('exam-sessions/{session}/answers', [ExamSessionController::class, 'saveAnswer'])->name('exam-sessions.answers.save');
});

// Shared profile
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Playground routes
Route::middleware(['auth', 'verified'])->prefix('playground')->name('playground.')->group(function () {
    Route::get('/', function () {
        return Inertia::render('Playground/Index');
    })->name('index');

    Route::get('tables', function () {
        return Inertia::render('Playground/Tables');
    })->name('tables');
});

require __DIR__.'/auth.php';
