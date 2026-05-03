<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClassController extends Controller
{
    public function store(Request $request)
    {
        $data = $this->validatedClassData($request);

        $attributes = [
            'name' => $data['name'],
            'created_by' => $request->user()->id,
        ];

        if (! empty($data['id'])) {
            $attributes['id'] = $data['id'];
        }

        $class = SchoolClass::create($attributes);
        $class->subjects()->sync($data['subject_ids'] ?? []);

        return redirect()->back()->with('success', 'Class created.');
    }

    public function update(Request $request, SchoolClass $class)
    {
        $data = $this->validatedClassData($request, $class);

        $class->update(['name' => $data['name']]);
        $class->subjects()->sync($data['subject_ids'] ?? []);

        return redirect()->back()->with('success', 'Class updated.');
    }

    public function destroy(SchoolClass $class)
    {
        $class->delete();

        return redirect()->back()->with('success', 'Class deleted.');
    }

    /**
     * @return array{id?: string|null, name: string, subject_ids?: array<int, string>}
     */
    private function validatedClassData(Request $request, ?SchoolClass $class = null): array
    {
        return $request->validate([
            'id' => [
                $class ? 'prohibited' : 'nullable',
                'string',
                'max:255',
                Rule::unique('school_classes', 'id'),
            ],
            'name' => 'required|string|max:255',
            'subject_ids' => 'nullable|array',
            'subject_ids.*' => 'string|exists:subjects,id',
        ]);
    }
}
