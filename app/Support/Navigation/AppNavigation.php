<?php

namespace App\Support\Navigation;

use App\Models\User;

class AppNavigation
{
    /**
     * @return array<int, array{label: string, href: string, match: string, icon: string}>
     */
    public function for(User $user): array
    {
        if ($user->isAdmin()) {
            return $this->admin();
        }

        if ($user->isLecturer()) {
            return $this->lecturer();
        }

        if ($user->isStudent()) {
            return $this->student();
        }

        return [];
    }

    /**
     * @return array<int, array{label: string, href: string, match: string, icon: string}>
     */
    private function admin(): array
    {
        return [
            [
                'label' => 'Admin',
                'href' => route('admin.dashboard'),
                'match' => 'admin.dashboard',
                'icon' => 'home',
            ],
        ];
    }

    /**
     * @return array<int, array{label: string, href: string, match: string, icon: string}>
     */
    private function lecturer(): array
    {
        return [
            [
                'label' => 'Overview',
                'href' => route('lecturer.dashboard'),
                'match' => 'lecturer.dashboard',
                'icon' => 'home',
            ],
            [
                'label' => 'Exams',
                'href' => route('lecturer.exams.index'),
                'match' => 'lecturer.exams.*',
                'icon' => 'doc',
            ],
            [
                'label' => 'Classes',
                'href' => route('lecturer.classes.index'),
                'match' => 'lecturer.classes.*',
                'icon' => 'users',
            ],
            [
                'label' => 'Subjects',
                'href' => route('lecturer.subjects.index'),
                'match' => 'lecturer.subjects.*',
                'icon' => 'book',
            ],
        ];
    }

    /**
     * @return array<int, array{label: string, href: string, match: string, icon: string}>
     */
    private function student(): array
    {
        return [
            [
                'label' => 'Dashboard',
                'href' => route('student.dashboard'),
                'match' => 'student.dashboard',
                'icon' => 'home',
            ],
            [
                'label' => 'Available Exams',
                'href' => route('student.exams.index'),
                'match' => 'student.exams.*',
                'icon' => 'doc',
            ],
        ];
    }
}
