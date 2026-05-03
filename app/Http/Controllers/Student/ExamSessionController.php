<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Answer;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\User;
use App\States\ExamSession\Pending;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class ExamSessionController extends Controller
{
    /** GET /student/exams */
    public function index(Request $request)
    {
        $student = $request->user();
        $activeClass = $student->activeClass();
        $noClass = $activeClass === null;

        $exams = $noClass ? collect() : Exam::with([
            'subject',
            'sessions' => fn ($query) => $query
                ->where('user_id', $student->id)
                ->latest(),
        ])
            ->whereIn('subject_id', $activeClass->subjects->pluck('id'))
            ->where('status', 'active')
            ->get()
            ->map(fn (Exam $exam) => $this->examRow($exam));

        return Inertia::render('Student/Exams/Index', compact('exams', 'noClass'));
    }

    /** POST /student/exams/{exam}/start */
    public function start(Request $request, Exam $exam)
    {
        $student = $request->user();
        $this->authorizeExamAccess($student, $exam);
        $this->authorizeExamCanStart($exam);

        $existing = ExamSession::where('exam_id', $exam->id)
            ->where('user_id', $student->id)
            ->latest()
            ->first();

        if ($existing?->state instanceof Pending) {
            return redirect()->route('student.exam-sessions.show', $existing);
        }

        if ($existing !== null) {
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

    /** GET /student/exam-sessions */
    public function listSessions(Request $request)
    {
        $sessions = ExamSession::with(['exam.subject'])
            ->where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (ExamSession $session) => $this->sessionRow($session));

        return Inertia::render('Student/ExamSessions/Index', compact('sessions'));
    }

    /** GET /student/exam-sessions/{session} */
    public function showSession(Request $request, ExamSession $session)
    {
        $this->authorizeSessionAccess($request->user(), $session);

        $session->load(['exam.subject', 'exam.questions.options', 'answers.question', 'answers.selectedOption']);

        if ($session->state instanceof Pending && $session->shouldAutoSubmitAbandoned()) {
            $session->autoSubmit();
            $session->refresh()->load(['exam.subject', 'exam.questions.options', 'answers.question', 'answers.selectedOption']);
        }

        if ($session->state instanceof Pending) {
            return Inertia::render('Student/ExamSessions/Take', $this->takePayload($session));
        }

        return Inertia::render('Student/ExamSessions/Show', [
            'session' => $this->resultPayload($session),
        ]);
    }

    /** POST /student/exam-sessions/{session}/submit */
    public function submit(Request $request, ExamSession $session)
    {
        $this->authorizeSessionAccess($request->user(), $session);
        abort_unless($session->state instanceof Pending, 403, 'Session already submitted.');

        if (! $request->boolean('auto_submitted') && ! $session->shouldAutoSubmitAbandoned()) {
            $this->ensureRequiredMcqsAreAnswered($session);
        }

        $session->submit();

        return redirect()->route('student.exam-sessions.show', $session);
    }

    /** POST /student/exam-sessions/{session}/answers */
    public function saveAnswer(Request $request, ExamSession $session)
    {
        $this->authorizeSessionAccess($request->user(), $session);
        abort_unless($session->state instanceof Pending, 403);
        abort_unless($session->canSaveAnswers(), 403, 'The exam time has ended.');

        $questionData = $request->validate([
            'question_id' => ['required', 'integer'],
        ]);

        $question = $session->exam->questions()->findOrFail($questionData['question_id']);

        $data = Validator::make(
            $request->all(),
            $question->isMcq()
                ? [
                    'selected_option_id' => [
                        'nullable',
                        'integer',
                        Rule::exists('options', 'id')->where('question_id', $question->id),
                    ],
                ]
                : ['text_answer' => ['nullable', 'string']]
        )->validate();

        Answer::updateOrCreate(
            ['exam_session_id' => $session->id, 'question_id' => $question->id],
            array_merge(
                ['type' => $question->type],
                $question->isMcq()
                    ? ['selected_option_id' => $data['selected_option_id'] ?? null]
                    : ['text_answer' => $data['text_answer'] ?? '']
            )
        );

        return redirect()->back();
    }

    private function authorizeSessionAccess(User $student, ExamSession $session): void
    {
        abort_unless($session->user_id === $student->id, 403);

        $session->loadMissing('exam');
        $this->authorizeExamAccess($student, $session->exam);
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

    private function authorizeExamCanStart(Exam $exam): void
    {
        abort_unless($exam->status === 'active', 403, 'Exam is not active.');
        abort_unless(
            $exam->starts_at && now()->greaterThanOrEqualTo($exam->starts_at),
            403,
            'Exam has not started yet.'
        );
        abort_unless(
            $exam->ends_at && now()->lessThanOrEqualTo($exam->ends_at),
            403,
            'Exam has already ended.'
        );
    }

    private function ensureRequiredMcqsAreAnswered(ExamSession $session): void
    {
        $answeredQuestionIds = $session->answers()
            ->where('type', 'mcq')
            ->whereNotNull('selected_option_id')
            ->pluck('question_id');

        $missingMcqCount = $session->exam->questions()
            ->where('type', 'mcq')
            ->whereNotIn('id', $answeredQuestionIds)
            ->count();

        if ($missingMcqCount > 0) {
            throw ValidationException::withMessages([
                'answers' => 'Answer and save all multiple-choice questions before submitting.',
            ]);
        }
    }

    private function examRow(Exam $exam): array
    {
        $session = $exam->sessions->first();

        return [
            'id' => $exam->id,
            'title' => $exam->title,
            'subject' => $exam->subject->name,
            'time_limit' => $exam->time_limit_minutes,
            'ends_at' => $exam->ends_at,
            'attempt_state' => $session ? (string) $session->state : null,
            'session_id' => $session?->id,
            'score_label' => $session?->scoreLabel(),
        ];
    }

    private function sessionRow(ExamSession $session): array
    {
        return [
            'id' => $session->id,
            'exam_title' => $session->exam->title,
            'subject' => $session->exam->subject->name,
            'state' => (string) $session->state,
            'score_label' => $session->scoreLabel(),
            'submitted_at' => $session->submitted_at,
        ];
    }

    private function takePayload(ExamSession $session): array
    {
        return [
            'session' => [
                'id' => $session->id,
                'state' => (string) $session->state,
                'started_at' => $session->started_at,
                'deadline' => $session->deadline(),
                'exam' => [
                    'id' => $session->exam->id,
                    'title' => $session->exam->title,
                    'questions' => $session->exam->questions->map(fn ($question) => [
                        'id' => $question->id,
                        'type' => $question->type,
                        'body' => $question->text,
                        'points' => $question->effectiveWeight(),
                        'options' => $question->options->map(fn ($option) => [
                            'id' => $option->id,
                            'body' => $option->body,
                        ])->values(),
                    ])->values(),
                ],
            ],
            'answeredMap' => $session->answers->mapWithKeys(fn (Answer $answer) => [
                $answer->question_id => [
                    'selected_option_id' => $answer->selected_option_id,
                    'text_answer' => $answer->text_answer,
                ],
            ]),
        ];
    }

    private function resultPayload(ExamSession $session): array
    {
        return [
            'id' => $session->id,
            'state' => (string) $session->state,
            'submitted_at' => $session->submitted_at,
            'score' => $session->score_raw !== null
                ? [
                    'raw' => $session->score_raw,
                    'max' => $session->score_max,
                    'label' => $session->scoreLabel(),
                    'percent' => $session->scorePercent(),
                ]
                : null,
            'exam' => [
                'id' => $session->exam->id,
                'title' => $session->exam->title,
            ],
            'answers' => $session->answers->map(fn (Answer $answer) => [
                'type' => $answer->type,
                'question' => [
                    'text' => $answer->question->text,
                    'weight' => $answer->question->effectiveWeight(),
                ],
                'selected_option' => $answer->selectedOption ? [
                    'body' => $answer->selectedOption->body,
                    'is_correct' => $answer->selectedOption->is_correct,
                ] : null,
                'text_answer' => $answer->text_answer,
                'score' => $answer->score,
                'reviewer_comment' => $answer->reviewer_comment,
            ]),
        ];
    }
}
