<?php

namespace App\Policies;

use App\Models\Quiz;
use App\Models\User;

class QuizPolicy
{
    public function manage(User $user, Quiz $quiz): bool
    {
        return $user->isFaculty() && $quiz->classroom->faculty_id === $user->id;
    }

    public function participate(User $user, Quiz $quiz): bool
    {
        return $user->isStudent()
            && $quiz->classroom->students()->whereKey($user->id)->exists();
    }
}
