<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClassController extends Controller
{
    public function index(Request $request)
    {
        $classes = SchoolClass::with([
            'subjects' => fn ($query) => $query->orderBy('name'),
            'students' => fn ($query) => $query->wherePivotNull('unassigned_at'),
        ])
            ->whereHas('subjects', fn ($query) => $query->where('subjects.created_by', $request->user()->id))
            ->orderBy('name')
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'students' => $c->students->count(),
                'subjects' => $c->subjects->map(fn ($s) => ['id' => $s->id, 'name' => $s->name]),
            ]);

        return Inertia::render('Lecturer/Classes/Index', compact('classes'));
    }

    public function show(SchoolClass $class)
    {
        $this->authorize('view', $class);
        $class->load([
            'subjects' => fn ($query) => $query->orderBy('name'),
            'students' => fn ($query) => $query->wherePivotNull('unassigned_at'),
        ]);

        return Inertia::render('Lecturer/Classes/Show', [
            'schoolClass' => [
                'id' => $class->id,
                'name' => $class->name,
                'subjects' => $class->subjects->map(fn ($s) => ['id' => $s->id, 'name' => $s->name]),
                'students' => $class->students->map(fn ($s) => ['id' => $s->id, 'name' => $s->name, 'email' => $s->email]),
            ],
        ]);
    }
}
