<?php

namespace App\Enums;

enum UserRole: string
{
    case Lecturer = 'lecturer';
    case Student = 'student';

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::Lecturer => 'Lecturer',
            self::Student => 'Student',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Lecturer => 'lecturer',
            self::Student => 'student',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Lecturer => 'Create exams, review submissions, and guide student progress.',
            self::Student => 'Take exams, track attempts, and keep upcoming assessments clear.',
        };
    }
}
