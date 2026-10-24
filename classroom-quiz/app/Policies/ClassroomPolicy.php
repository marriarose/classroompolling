<?php

namespace App\Policies;

use App\Models\Classroom;
use App\Models\User;

class ClassroomPolicy
{
    /** Faculty who own the classroom. */
    public function manage(User $user, Classroom $classroom): bool
    {
        return $user->isFaculty() && $classroom->faculty_id === $user->id;
    }

    /** Students who joined the classroom. */
    public function participate(User $user, Classroom $classroom): bool
    {
        return $user->isStudent()
            && $classroom->students()->whereKey($user->id)->exists();
    }
}
