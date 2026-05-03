<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Question;
use Illuminate\Http\Request;
use Inertia\Inertia;

class QuestionController extends Controller
{
    public function create(Exam $exam)
    {
        $this->authorize('update', $exam);

        return Inertia::render('Lecturer/Questions/Create', [
            'exam' => [
                'id' => $exam->id,
                'title' => $exam->title,
            ],
        ]);
    }

    public function store(Request $request, Exam $exam)
    {
        $this->authorize('update', $exam);

        $data = $request->validate([
            'type' => 'required|in:mcq,open_text',
            'text' => 'required|string',
            'weight' => 'nullable|numeric|min:0.01',
            'options' => 'array',
            'options.*.text' => 'required_if:type,mcq|string',
            'options.*.is_correct' => 'required_if:type,mcq|boolean',
        ]);

        if ($data['type'] === 'mcq') {
            $options = $data['options'] ?? [];
            $correctCount = collect($options)->filter(fn ($o) => $o['is_correct'])->count();

            if (count($options) < 2) {
                return back()->withErrors(['options' => 'MCQ must have at least 2 options.']);
            }
            if ($correctCount !== 1) {
                return back()->withErrors(['options' => 'MCQ must have exactly one correct option.']);
            }
        }

        $order = $exam->questions()->max('order') + 1;
        $question = $exam->questions()->create([
            'type' => $data['type'],
            'text' => $data['text'],
            'weight' => $data['weight'] ?? null,
            'order' => $order,
        ]);

        if ($data['type'] === 'mcq') {
            foreach ($data['options'] as $i => $opt) {
                $question->options()->create([
                    'body' => $opt['text'],
                    'is_correct' => (bool) $opt['is_correct'],
                    'order' => $i + 1,
                ]);
            }
        }

        return redirect()->route('lecturer.exams.show', $exam)->with('success', 'Question added.');
    }

    public function edit(Question $question)
    {
        $this->authorize('view', $question);
        $question->load('options');

        return Inertia::render('Lecturer/Questions/Edit', [
            'question' => [
                'id' => $question->id,
                'exam_id' => $question->exam_id,
                'type' => $question->type,
                'text' => $question->text,
                'weight' => $question->weight,
                'options' => $question->options->map(fn ($o) => [
                    'id' => $o->id,
                    'body' => $o->body,
                    'is_correct' => $o->is_correct,
                ]),
            ],
        ]);
    }

    public function update(Request $request, Question $question)
    {
        $this->authorize('update', $question);

        $data = $request->validate([
            'text' => 'required|string',
            'weight' => 'nullable|numeric|min:0.01',
            'options' => 'array',
            'options.*.text' => 'required_if:type,mcq|string',
            'options.*.is_correct' => 'boolean',
        ]);

        $question->update(['text' => $data['text'], 'weight' => $data['weight'] ?? null]);

        if ($question->isMcq() && ! empty($data['options'])) {
            $question->options()->delete();
            foreach ($data['options'] as $i => $opt) {
                $question->options()->create([
                    'body' => $opt['text'],
                    'is_correct' => (bool) ($opt['is_correct'] ?? false),
                    'order' => $i + 1,
                ]);
            }
        }

        return redirect()->route('lecturer.exams.show', $question->exam_id)->with('success', 'Question updated.');
    }

    public function destroy(Question $question)
    {
        $this->authorize('delete', $question);
        $examId = $question->exam_id;
        $question->delete();

        return redirect()->route('lecturer.exams.show', $examId)->with('success', 'Question deleted.');
    }
}
