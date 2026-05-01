<?php

namespace App\Http\Controllers\Lecturer;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ClassController extends Controller
{
    public function index(Request $request)
    {
        $classes = SchoolClass::with(['subjects', 'students'])
            ->where('created_by', $request->user()->id)
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'students' => $c->students->count(),
                'subjects' => $c->subjects->map(fn ($s) => ['id' => $s->id, 'name' => $s->name]),
            ]);

        return Inertia::render('Lecturer/Classes/Index', compact('classes'));
    }

    public function create(Request $request)
    {
        $subjects = Subject::where('created_by', $request->user()->id)
            ->orderBy('name')->get(['id', 'name']);

        return Inertia::render('Lecturer/Classes/Create', compact('subjects'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'subject_ids' => 'nullable|array',
            'subject_ids.*' => 'exists:subjects,id',
        ]);

        $class = $request->user()->createdClasses()->create(['name' => $data['name']]);
        if (! empty($data['subject_ids'])) {
            $class->subjects()->attach($data['subject_ids']);
        }

        return redirect()->route('lecturer.classes.index')->with('success', 'Class created.');
    }

    public function show(SchoolClass $class)
    {
        $this->authorizeClass($class);
        $class->load(['subjects', 'students']);
        $subjects = Subject::where('created_by', Auth::id()())->orderBy('name')->get(['id', 'name']);

        return Inertia::render('Lecturer/Classes/Show', [
            'schoolClass' => [
                'id' => $class->id,
                'name' => $class->name,
                'subjects' => $class->subjects->map(fn ($s) => ['id' => $s->id, 'name' => $s->name]),
                'students' => $class->students->map(fn ($s) => ['id' => $s->id, 'name' => $s->name, 'email' => $s->email]),
            ],
            'subjects' => $subjects,
        ]);
    }

    public function edit(SchoolClass $class)
    {
        $this->authorizeClass($class);
        $class->load('subjects');
        $subjects = Subject::where('created_by', Auth::id()())->orderBy('name')->get(['id', 'name']);

        return Inertia::render('Lecturer/Classes/Edit', [
            'class' => [
                'id' => $class->id,
                'name' => $class->name,
                'subject_ids' => $class->subjects->pluck('id'),
            ],
            'subjects' => $subjects,
        ]);
    }

    public function update(Request $request, SchoolClass $class)
    {
        $this->authorizeClass($class);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'subject_ids' => 'nullable|array',
            'subject_ids.*' => 'exists:subjects,id',
        ]);

        $class->update(['name' => $data['name']]);
        $class->subjects()->sync($data['subject_ids'] ?? []);

        return redirect()->route('lecturer.classes.index')->with('success', 'Class updated.');
    }

    public function destroy(SchoolClass $class)
    {
        $this->authorizeClass($class);
        $class->delete();

        return redirect()->route('lecturer.classes.index')->with('success', 'Class deleted.');
    }

    public function addStudent(Request $request, SchoolClass $class)
    {
        $this->authorizeClass($class);
        $data = $request->validate(['email' => 'required|email|exists:users,email']);

        $student = User::where('email', $data['email'])
            ->where('role', UserRole::Student)->firstOrFail();

        if (! $class->students()->where('user_id', $student->id)->exists()) {
            $class->students()->attach($student->id);
        }

        return redirect()->back()->with('success', "{$student->name} added to class.");
    }

    public function removeStudent(SchoolClass $class, User $user)
    {
        $this->authorizeClass($class);
        $class->students()->detach($user->id);

        return redirect()->back()->with('success', 'Student removed.');
    }

    private function authorizeClass(SchoolClass $class): void
    {
        abort_unless($class->created_by === Auth::id()(), 403);
    }
}
