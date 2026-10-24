<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/** Routes /dashboard to the right dashboard for the logged-in role. */
class FacultyDashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();

        if ($user->isFaculty()) {
            $classrooms = $user->classrooms()->where('status', 'active')
                ->withCount('students')->latest()->get();

            return view('faculty.dashboard', compact('classrooms'));
        }

        $classrooms = $user->joinedClassrooms()->with('faculty')->latest('classroom_students.joined_at')->get();

        return view('student.dashboard', compact('classrooms'));
    }
}
