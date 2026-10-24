<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClassroomController;
use App\Http\Controllers\FacultyDashboardController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\ResponseController;
use App\Http\Controllers\ResultController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route(auth()->check() ? 'dashboard' : 'login'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', FacultyDashboardController::class)->name('dashboard');

    // ---------- Faculty ----------
    Route::middleware('role:faculty')->prefix('faculty')->name('faculty.')->group(function () {
        Route::get('classrooms', [ClassroomController::class, 'index'])->name('classrooms.index');
        Route::get('classrooms/create', [ClassroomController::class, 'create'])->name('classrooms.create');
        Route::post('classrooms', [ClassroomController::class, 'store'])->name('classrooms.store');
        Route::get('classrooms/{classroom}', [ClassroomController::class, 'show'])->name('classrooms.show');
        Route::patch('classrooms/{classroom}/status', [ClassroomController::class, 'toggle'])->name('classrooms.toggle');
        Route::get('classrooms/{classroom}/students', [ClassroomController::class, 'students'])->name('classrooms.students');

        Route::post('classrooms/{classroom}/quiz', [QuizController::class, 'store'])->name('quizzes.store');
        Route::get('quizzes/{quiz}', [QuizController::class, 'show'])->name('quizzes.show');
        Route::post('quizzes/{quiz}/questions', [QuestionController::class, 'store'])->name('questions.store');
        Route::delete('questions/{question}', [QuestionController::class, 'destroy'])->name('questions.destroy');
        Route::post('quizzes/{quiz}/start', [QuizController::class, 'start'])->name('quizzes.start');
        Route::post('quizzes/{quiz}/close', [QuizController::class, 'close'])->name('quizzes.close');
        Route::get('quizzes/{quiz}/participation', [QuizController::class, 'participation'])->name('quizzes.participation');

        Route::get('results', [ResultController::class, 'index'])->name('results.index');
        Route::get('quizzes/{quiz}/results', [ResultController::class, 'show'])->name('quizzes.results');
        Route::get('quizzes/{quiz}/results.json', [ResultController::class, 'json'])->name('quizzes.results.json');
    });

    // ---------- Student ----------
    Route::middleware('role:student')->group(function () {
        Route::get('/join', [StudentController::class, 'joinForm'])->name('student.join');
        Route::post('/join', [StudentController::class, 'join'])->name('student.join.store');
        Route::get('/student/classrooms/{classroom}', [StudentController::class, 'classroom'])->name('student.classrooms.show');
        Route::get('/student/classrooms/{classroom}/status', [StudentController::class, 'classroomStatus'])->name('student.classrooms.status');
        Route::get('/student/quizzes/{quiz}', [StudentController::class, 'quiz'])->name('student.quizzes.show');
        Route::get('/student/quizzes/{quiz}/status', [StudentController::class, 'status'])->name('student.quizzes.status');
        Route::post('/student/quizzes/{quiz}/answer', [ResponseController::class, 'store'])->name('student.quizzes.answer');
        Route::get('/student/quizzes/{quiz}/result', [StudentController::class, 'result'])->name('student.quizzes.result');
    });
});
