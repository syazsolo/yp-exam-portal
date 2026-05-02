<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Answer;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\User;
use App\States\ExamSession\Pending;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ExamSessionController extends Controller
{
    /** GET /student/exams — list exams available to student's active class */
    public function index(Request $request)
    {
        $student = $request->user();
        $activeClass = $student->activeClass();
        $noClass = $activeClass === null;

        $exams = $noClass ? collect() : Exam::with('subject')
            ->whereIn('subject_id', $activeClass->subjects->pluck('id'))
            ->where('status', 'active')
            ->whereDoesntHave('sessions', fn ($q) => $q->where('user_id', $student->id))
            ->get()
            ->map(fn ($e) => [
                'id' => $e->id,
                'title' => $e->title,
                'subject' => $e->subject->name,
                'time_limit' => $e->time_limit_minutes,
                'ends_at' => $e->ends_at,
            ]);

        return Inertia::render('Student/Exams/Index', compact('exams', 'noClass'));
    }

    /** GET /student/exams/{exam} — show exam detail (access check only) */
    public function showExam(Request $request, Exam $exam)
    {
        $this->authorizeExamAccess($request->user(), $exam);

        return Inertia::render('Student/Exams/Show', [
            'exam' => [
                'id' => $exam->id,
                'title' => $exam->title,
                'subject' => $exam->subject->name,
            ],
        ]);
    }

    /** POST /student/exams/{exam}/sessions — start exam */
    public function start(Request $request, Exam $exam)
    {
        $student = $request->user();
        $this->authorizeExamAccess($student, $exam);

        // Time window check
        abort_unless(
            $exam->starts_at && now()->greaterThanOrEqualTo($exam->starts_at),
            403, 'Exam has not started yet.'
        );
        abort_unless(
            $exam->ends_at && now()->lessThanOrEqualTo($exam->ends_at),
            403, 'Exam has already ended.'
        );

        // Duplicate session check
        if (ExamSession::where('exam_id', $exam->id)->where('user_id', $student->id)->exists()) {
            abort(409, 'Already attempted this exam.');
        }

        $session = ExamSession::create([
            'exam_id' => $exam->id,
            'user_id' => $student->id,
            'state' => 'pending',
            'started_at' => now(),
        ]);

        return redirect()->route('student.exam-sessions.show', $session);
    }

    /** GET /student/exam-sessions — list past sessions */
    public function listSessions(Request $request)
    {
        $sessions = ExamSession::with(['exam.subject'])
            ->where('user_id', $request->user()->id)
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

        return Inertia::render('Student/ExamSessions/Index', compact('sessions'));
    }

    /** GET /student/exam-sessions/{session} — view a past session */
    public function showSession(ExamSession $session)
    {
        abort_unless($session->user_id === Auth::id(), 403);

        $session->load(['exam.questions.options', 'answers.question', 'answers.selectedOption']);

        return Inertia::render('Student/ExamSessions/Show', [
            'session' => [
                'id' => $session->id,
                'state' => (string) $session->state,
                'submitted_at' => $session->submitted_at,
                'score' => $session->score_raw !== null
                    ? ['raw' => $session->score_raw, 'max' => $session->score_max, 'label' => $session->scoreLabel(), 'percent' => $session->scorePercent()]
                    : null,
                'exam' => ['id' => $session->exam->id, 'title' => $session->exam->title],
                'answers' => $session->answers->map(fn ($a) => [
                    'type' => $a->type,
                    'question' => ['text' => $a->question->text, 'weight' => $a->question->effectiveWeight()],
                    'selected_option' => $a->selectedOption ? ['body' => $a->selectedOption->body, 'is_correct' => $a->selectedOption->is_correct] : null,
                    'text_answer' => $a->text_answer,
                    'score' => $a->score,
                    'reviewer_comment' => $a->reviewer_comment,
                ]),
            ],
        ]);
    }

    /** POST /student/exam-sessions/{session}/submit */
    public function submit(ExamSession $session)
    {
        abort_unless($session->user_id === Auth::id(), 403);
        abort_unless($session->state instanceof Pending, 403, 'Session already submitted.');

        $session->submit();

        return redirect()->route('student.exam-sessions.show', $session);
    }

    /** POST /student/exam-sessions/{session}/answers */
    public function saveAnswer(Request $request, ExamSession $session)
    {
        abort_unless($session->user_id === Auth::id(), 403);
        abort_unless($session->state instanceof Pending, 403);

        $data = $request->validate([
            'question_id' => 'required|exists:questions,id',
            'selected_option_id' => 'nullable|exists:options,id',
            'text_answer' => 'nullable|string',
        ]);

        $question = $session->exam->questions()->findOrFail($data['question_id']);

        Answer::updateOrCreate(
            ['exam_session_id' => $session->id, 'question_id' => $question->id],
            array_merge(
                ['type' => $question->type],
                $question->isMcq()
                    ? ['selected_option_id' => $data['selected_option_id']]
                    : ['text_answer' => $data['text_answer']]
            )
        );

        return redirect()->back();
    }

    private function authorizeExamAccess(User $student, Exam $exam): void
    {
        $activeClass = $student->activeClass();
        abort_if($activeClass === null, 403, 'Not assigned to a class.');

        $hasSubject = $activeClass->subjects()
            ->where('subjects.id', $exam->subject_id)
            ->exists();

        abort_unless($hasSubject, 403, 'Exam subject not in your class.');
    }
}
