<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ExamController extends Controller
{
    public function index(Request $request)
    {
        $exams = Exam::with(['subject', 'sessions'])
            ->where('created_by', $request->user()->id)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($e) => [
                'id' => $e->id,
                'title' => $e->title,
                'subject' => $e->subject->name,
                'status' => $e->status,
                'time_limit' => $e->time_limit_minutes,
                'starts_at' => $e->starts_at,
                'ends_at' => $e->ends_at,
                'sessions_count' => $e->sessions->count(),
            ]);

        return Inertia::render('Lecturer/Exams/Index', compact('exams'));
    }

    public function create(Request $request)
    {
        $subjects = Subject::where('created_by', $request->user()->id)
            ->orderBy('name')->get(['id', 'name']);

        return Inertia::render('Lecturer/Exams/Create', compact('subjects'));
    }

    /** Handles POST /lecturer/exams and POST /lecturer/subjects/{subject}/exams */
    public function store(Request $request, ?Subject $subject = null)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'subject_id' => [$subject ? 'nullable' : 'required', 'exists:subjects,id'],
            'time_limit_minutes' => 'required|integer|min:1|max:300',
            'default_question_weight' => 'nullable|numeric|min:0.01',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after:starts_at',
        ]);

        $exam = Exam::create([
            ...$data,
            'subject_id' => $subject?->id ?? $data['subject_id'],
            'created_by' => $request->user()->id,
            'default_question_weight' => $data['default_question_weight'] ?? 1.0,
            'status' => 'draft',
        ]);

        return redirect()->route('lecturer.exams.show', $exam)
            ->with('success', 'Exam created. Add questions now.');
    }

    public function show(Exam $exam)
    {
        // Any lecturer can view any exam — ownership only enforced on mutations.
        $exam->load(['subject', 'questions.options', 'sessions.student']);

        $sessions = $exam->sessions->map(fn ($s) => [
            'id' => $s->id,
            'student' => $s->student->name,
            'state' => (string) $s->state,
            'score_label' => $s->scoreLabel(),
            'submitted_at' => $s->submitted_at,
        ]);

        return Inertia::render('Lecturer/Exams/Show', [
            'exam' => [
                'id' => $exam->id,
                'title' => $exam->title,
                'subject' => $exam->subject->name,
                'subject_id' => $exam->subject_id,
                'status' => $exam->status,
                'time_limit' => $exam->time_limit_minutes,
                'default_question_weight' => $exam->default_question_weight,
                'starts_at' => $exam->starts_at,
                'ends_at' => $exam->ends_at,
                'questions' => $exam->questions->map(fn ($q) => [
                    'id' => $q->id,
                    'type' => $q->type,
                    'text' => $q->text,
                    'weight' => $q->effectiveWeight(),
                    'order' => $q->order,
                    'options' => $q->options->map(fn ($o) => [
                        'id' => $o->id,
                        'body' => $o->body,
                        'is_correct' => $o->is_correct,
                    ]),
                ]),
            ],
            'sessions' => $sessions,
        ]);
    }

    public function edit(Exam $exam)
    {
        $this->authorizeExam($exam);
        abort_if($exam->status === 'active', 403, 'Cannot edit an active exam.');

        $subjects = Subject::where('created_by', Auth::id())->orderBy('name')->get(['id', 'name']);

        return Inertia::render('Lecturer/Exams/Edit', [
            'exam' => [
                'id' => $exam->id,
                'title' => $exam->title,
                'subject_id' => $exam->subject_id,
                'time_limit_minutes' => $exam->time_limit_minutes,
                'default_question_weight' => $exam->default_question_weight,
                'starts_at' => $exam->starts_at,
                'ends_at' => $exam->ends_at,
            ],
            'subjects' => $subjects,
        ]);
    }

    public function update(Request $request, Exam $exam)
    {
        $this->authorizeExam($exam);
        abort_if($exam->status === 'active', 403, 'Cannot edit an active exam.');

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'subject_id' => 'required|exists:subjects,id',
            'time_limit_minutes' => 'required|integer|min:1|max:300',
            'default_question_weight' => 'nullable|numeric|min:0.01',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after:starts_at',
        ]);

        $exam->update($data);

        return redirect()->route('lecturer.exams.show', $exam)->with('success', 'Exam updated.');
    }

    public function destroy(Exam $exam)
    {
        $this->authorizeExam($exam);
        abort_if($exam->status === 'active', 403, 'Cannot delete an active exam.');
        $exam->delete();

        return redirect()->route('lecturer.exams.index')->with('success', 'Exam deleted.');
    }

    private function authorizeExam(Exam $exam): void
    {
        abort_unless($exam->created_by === Auth::id(), 403);
    }
}
