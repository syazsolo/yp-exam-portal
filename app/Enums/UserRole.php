<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Lecturer = 'lecturer';
    case Student = 'student';

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::Lecturer => 'Lecturer',
            self::Student => 'Student',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Admin => 'admin',
            self::Lecturer => 'lecturer',
            self::Student => 'student',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Admin => 'Manage student enrollment and class assignments.',
            self::Lecturer => 'Create exams, review submissions, and guide student progress.',
            self::Student => 'Take exams, track attempts, and keep upcoming assessments clear.',
        };
    }

    public function dashboardRoute(): string
    {
        return match ($this) {
            self::Admin => 'admin.dashboard',
            self::Lecturer => 'lecturer.dashboard',
            self::Student => 'student.dashboard',
        };
    }
}
