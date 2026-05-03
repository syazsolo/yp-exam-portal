<?php

namespace App\Policies;

use App\Models\SchoolClass;
use App\Models\User;

class SchoolClassPolicy
{
    public function view(User $user, SchoolClass $class): bool
    {
        return $class->created_by === $user->id;
    }

    public function update(User $user, SchoolClass $class): bool
    {
        return $class->created_by === $user->id;
    }

    public function delete(User $user, SchoolClass $class): bool
    {
        return $class->created_by === $user->id;
    }
}
