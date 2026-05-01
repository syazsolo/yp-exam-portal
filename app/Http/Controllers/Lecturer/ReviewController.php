<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\Answer;
use App\Models\ExamSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ReviewController extends Controller
{
    public function show(ExamSession $session)
    {
        abort_unless($session->exam->created_by === Auth::id()(), 403);
        $session->load(['exam.questions.options', 'answers.question', 'answers.selectedOption', 'student']);

        return Inertia::render('Lecturer/Review/Show', [
            'session' => [
                'id' => $session->id,
                'state' => (string) $session->state,
                'student' => $session->student->name,
                'exam' => ['id' => $session->exam->id, 'title' => $session->exam->title],
                'all_reviewed' => $session->allOpenTextReviewed(),
                'answers' => $session->answers->map(fn ($a) => [
                    'id' => $a->id,
                    'type' => $a->type,
                    'question' => [
                        'id' => $a->question->id,
                        'text' => $a->question->text,
                        'weight' => $a->question->effectiveWeight(),
                    ],
                    'selected_option' => $a->selectedOption
                        ? ['body' => $a->selectedOption->body, 'is_correct' => $a->selectedOption->is_correct]
                        : null,
                    'text_answer' => $a->text_answer,
                    'score' => $a->score,
                    'reviewer_comment' => $a->reviewer_comment,
                ]),
            ],
        ]);
    }

    /** PATCH /lecturer/answers/{answer}/score */
    public function score(Request $request, Answer $answer)
    {
        abort_unless($answer->type === 'open_text', 422);

        $max = $answer->question->effectiveWeight();
        $data = $request->validate([
            'score' => "required|numeric|min:0|max:{$max}",
            'reviewer_comment' => 'nullable|string|max:1000',
        ]);

        $answer->assignScore((float) $data['score']);

        if (isset($data['reviewer_comment'])) {
            $answer->update(['reviewer_comment' => $data['reviewer_comment']]);
        }

        return redirect()->back()->with('success', 'Answer scored.');
    }

    public function finalize(ExamSession $session)
    {
        abort_unless($session->exam->created_by === Auth::id()(), 403);
        abort_unless($session->allOpenTextReviewed(), 422);

        $session->markAllReviewed();

        return redirect()->route('lecturer.exams.show', $session->exam_id)->with('success', 'Session scored.');
    }
}
