<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Facades\Auth;

class ClassUser extends Pivot
{
    protected $table = 'class_user';

    public $incrementing = true;

    protected $fillable = ['user_id', 'class_id', 'assigned_at', 'unassigned_at'];

    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
            'unassigned_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::created(function (self $pivot) {
            // Only audit fresh enrollments (assigned_at present, unassigned_at null)
            if ($pivot->unassigned_at !== null) {
                return;
            }

            $class = SchoolClass::find($pivot->class_id);
            $student = User::find($pivot->user_id);

            if ($class && $student) {
                AuditLog::record('student.enrolled', $class, Auth::user(), $student);
            }
        });
    }
}
