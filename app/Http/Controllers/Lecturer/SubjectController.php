<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SubjectController extends Controller
{
    public function index(Request $request)
    {
        $subjects = Subject::with('classes')
            ->where('created_by', $request->user()->id)
            ->orderBy('name')
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'description' => $s->description,
                'class_count' => $s->classes->count(),
            ]);

        return Inertia::render('Lecturer/Subjects/Index', compact('subjects'));
    }

    public function create()
    {
        return Inertia::render('Lecturer/Subjects/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $request->user()->createdSubjects()->create($data);

        return redirect()->route('lecturer.subjects.index')->with('success', 'Subject created.');
    }

    public function show(Subject $subject)
    {
        $this->authorize('view', $subject);

        return redirect()->route('lecturer.subjects.edit', $subject);
    }

    public function edit(Subject $subject)
    {
        $this->authorize('update', $subject);

        return Inertia::render('Lecturer/Subjects/Edit', ['subject' => $subject]);
    }

    public function update(Request $request, Subject $subject)
    {
        $this->authorize('update', $subject);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $subject->update($data);

        return redirect()->route('lecturer.subjects.index')->with('success', 'Subject updated.');
    }

    public function destroy(Subject $subject)
    {
        $this->authorize('delete', $subject);
        $subject->delete();

        return redirect()->route('lecturer.subjects.index')->with('success', 'Subject deleted.');
    }
}
