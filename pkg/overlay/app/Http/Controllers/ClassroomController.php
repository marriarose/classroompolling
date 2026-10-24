<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClassroomRequest;
use App\Models\Classroom;
use Illuminate\Http\Request;

class ClassroomController extends Controller
{
    public function index(Request $request)
    {
        $classrooms = $request->user()->classrooms()->withCount('students')->latest()->get();

        return view('faculty.classrooms.index', compact('classrooms'));
    }

    public function create()
    {
        return view('faculty.classrooms.create');
    }

    public function store(StoreClassroomRequest $request)
    {
        $classroom = $request->user()->classrooms()->create([
            'name' => $request->validated('name'),
            'join_code' => Classroom::generateJoinCode(),
            'status' => 'active',
        ]);

        return redirect()->route('faculty.classrooms.show', $classroom)
            ->with('success', "Classroom created. Share join code {$classroom->join_code} with your students.");
    }

    public function show(Classroom $classroom)
    {
        $this->authorize('manage', $classroom);
        $classroom->load([
            'students',
            'quizzes' => fn ($q) => $q->withCount('questions')->latest(),
        ]);

        return view('faculty.classrooms.show', compact('classroom'));
    }

    public function toggle(Classroom $classroom)
    {
        $this->authorize('manage', $classroom);
        $classroom->update(['status' => $classroom->isActive() ? 'closed' : 'active']);

        return back()->with('success', 'Classroom is now '.$classroom->status.'.');
    }

    public function students(Classroom $classroom)
    {
        $this->authorize('manage', $classroom);

        return response()->json([
            'count' => $classroom->students()->count(),
            'students' => $classroom->students()->get()->map(fn ($s) => $s->pivot->display_name ?: $s->name)->values(),
        ]);
    }
}
