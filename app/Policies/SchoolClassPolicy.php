<?php

namespace App\Policies;

use App\Models\SchoolClass;
use App\Models\User;

class SchoolClassPolicy
{
    public function view(User $user, SchoolClass $class): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $class->subjects()
            ->where('subjects.created_by', $user->id)
            ->exists();
    }

    public function update(User $user, SchoolClass $class): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, SchoolClass $class): bool
    {
        return $user->isAdmin();
    }
}
