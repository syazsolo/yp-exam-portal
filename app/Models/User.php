<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements AuthenticatableContract
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'role', 'password'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    /** Roles available for self-registration (excludes Admin). */
    public static function roles(): array
    {
        return [UserRole::Lecturer->value, UserRole::Student->value];
    }

    public function isLecturer(): bool
    {
        return $this->role === UserRole::Lecturer;
    }

    public function isStudent(): bool
    {
        return $this->role === UserRole::Student;
    }

    public function dashboardRoute(): string
    {
        return $this->role->dashboardRoute();
    }

    public function roleLabel(): string
    {
        return $this->role->label();
    }

    /** All class enrollments including past (uses pivot unassigned_at to determine history). */
    public function classHistory(): BelongsToMany
    {
        return $this->belongsToMany(SchoolClass::class, 'class_user', 'user_id', 'class_id')
            ->using(ClassUser::class)
            ->withTrashed()
            ->withPivot(['assigned_at', 'unassigned_at'])
            ->orderByPivot('assigned_at');
    }

    /** Current active class (no unassigned_at set). */
    public function activeClass(): ?SchoolClass
    {
        return $this->classHistory()
            ->wherePivotNull('unassigned_at')
            ->first();
    }

    /** Accessor so $user->activeClass works as a property (in addition to the method). */
    public function getActiveClassAttribute(): ?SchoolClass
    {
        return $this->activeClass();
    }

    /** BelongsToMany for attach/detach — active only (no withTrashed). */
    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(SchoolClass::class, 'class_user', 'user_id', 'class_id')
            ->using(ClassUser::class)
            ->withPivot(['assigned_at', 'unassigned_at']);
    }

    public function examSessions(): HasMany
    {
        return $this->hasMany(ExamSession::class);
    }

    public function createdExams(): HasMany
    {
        return $this->hasMany(Exam::class, 'created_by');
    }

    public function createdSubjects(): HasMany
    {
        return $this->hasMany(Subject::class, 'created_by');
    }

    public function createdClasses(): HasMany
    {
        return $this->hasMany(SchoolClass::class, 'created_by');
    }
}
