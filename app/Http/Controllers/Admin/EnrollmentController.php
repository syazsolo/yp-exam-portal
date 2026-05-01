<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnrollmentController extends Controller
{
    public function enroll(Request $request, User $student)
    {
        $data = $request->validate([
            'class_id' => 'required|string|exists:school_classes,id',
        ]);

        // Close any current active enrollment
        $student->classes()
            ->wherePivotNull('unassigned_at')
            ->each(function (SchoolClass $class) use ($student) {
                $student->classes()->updateExistingPivot($class->id, [
                    'unassigned_at' => now(),
                ]);
            });

        // Attach new class
        $student->classes()->attach($data['class_id'], [
            'assigned_at' => now(),
            'unassigned_at' => null,
        ]);

        $class = SchoolClass::find($data['class_id']);

        AuditLog::record('student.enrolled', $class, Auth::user(), $student);

        return redirect()->back()->with('success', 'Student enrolled successfully.');
    }
}
