<?php

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

Route::get('/playground/tables', function () {
    return Inertia::render('Playground/Tables');
})->middleware(['auth', 'verified'])->name('playground.tables');

// Admin routes
Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::post('students/{student}/enroll', [EnrollmentController::class, 'enroll'])->name('students.enroll');
});

// Lecturer routes
Route::middleware(['auth', 'verified', 'lecturer'])->prefix('lecturer')->name('lecturer.')->group(function () {
    Route::get('/', LecturerDashboard::class)->name('dashboard');

    Route::resource('subjects', SubjectController::class);
    Route::resource('classes', ClassController::class);
    Route::post('classes/{class}/students', [ClassController::class, 'addStudent'])->name('classes.students.add');
    Route::delete('classes/{class}/students/{user}', [ClassController::class, 'removeStudent'])->name('classes.students.remove');

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
    Route::get('exams/{exam}', [ExamSessionController::class, 'showExam'])->name('exams.show');
    Route::post('exams/{exam}/sessions', [ExamSessionController::class, 'start'])->name('exams.sessions.start');

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

require __DIR__.'/auth.php';
