# ClassQuiz – Classroom Polling & Quiz System (Laravel 11 / PHP 8.2+ / MySQL)

Faculty create classrooms with a 6-character join code, build quizzes, start a round, watch participation live,
close the round and see a ranked scoreboard. Students join with the code, answer from any device, and see
their score once the round closes. **All scoring is server-side.**

## Features
- Faculty/Student registration & login (Laravel session auth, role column)
- Classrooms with unique, case-insensitive, unambiguous join codes (no `0 O 1 I`)
- Quiz builder: 2–4 options per question, one correct answer, points per question
- Start / Close round (confirmation + transaction + row lock), previous results list
- AJAX polling (3–4 s) for student waiting rooms, quiz status, faculty participation, student lists
- Scoreboard (rank, correct, score, %), aggregate stats, per-question correct/incorrect
- Policies for every faculty/student resource, Form Requests, CSRF on every form, feature tests

## Stack
Laravel 11, PHP 8.2+, MySQL, Eloquent, Blade, Bootstrap 5 (CDN), vanilla JS. No WebSockets required.

## Installation
This repository contains the **application layer** (everything you'd edit) as an overlay on a stock Laravel 11
skeleton, so you always get a fresh `composer.json`/`vendor`. Requirements: PHP 8.2+, Composer, MySQL 8 / MariaDB 10.6+.

```bash
./setup.sh classroom-quiz          # = composer create-project + copy overlay + .env (MySQL) + key:generate
cd classroom-quiz
# create an empty database named classroom_quiz and set DB_USERNAME / DB_PASSWORD in .env
php artisan migrate --seed
php artisan serve
```
Manual equivalent: `composer create-project laravel/laravel classroom-quiz "^11.0"`, copy `overlay/*` over it
(overwrites `bootstrap/app.php`, `routes/web.php`, `UserFactory`, `DatabaseSeeder`), set `DB_*` in `.env`,
`php artisan key:generate`, `php artisan migrate --seed`, `php artisan serve`.
(Laravel 12 should work too; `bootstrap/app.php` uses the same API.)

## Demo credentials (after `migrate --seed`)
| Role | Email | Password |
|---|---|---|
| Faculty | faculty@example.com | password |
| Student | student@example.com | password |

Other seeded students: anu@, maria@, rahul@, neha@ `example.com` (password `password`).
Seeded rooms: **AI Fundamentals `DEMK42`** (closed quiz with sample results) and **Machine Learning `MLRN26`**
(draft quiz ready to start).

## Workflows
**Faculty:** log in → *Create Classroom* → share code → open room (live student list) → *Create quiz* → add
questions → *Start Quiz* → watch live participation → *Close Round* (confirm) → results & scoreboard.
**Student:** log in/register → enter code + name on dashboard (`/join`) → waiting room → question appears →
pick option → *Submit* → next question / waiting → round closes → score & rank page.
Quick 2-device test: faculty in one browser, student in a private window.

## Tests
```bash
php artisan test        # uses phpunit.xml's in-memory SQLite by default
```
`tests/Feature/QuizFlowTest.php` covers classrooms, joining, quiz lifecycle, scoring, duplicates, closed/draft
submissions, tampered payloads, authorization and ranking.

## Structure
```
app/Http/Controllers   Auth, FacultyDashboard, Classroom, Quiz, Question, Student, Response, Result
app/Http/Requests      StoreClassroom, StoreQuiz, StoreQuestion, JoinClassroom, Answer
app/Http/Middleware    EnsureRole (role:faculty / role:student)
app/Models             User, Classroom, ClassroomStudent(pivot), Quiz, Question, Option, Response
app/Policies           ClassroomPolicy, QuizPolicy
app/Services           QuizScoringService (answer validation, scoring, scoreboard, stats)
database/              migrations, UserFactory, DatabaseSeeder
resources/views        layouts, components (navbar, alert, card, question), auth, faculty, student
public/js/poll.js      polling helper      public/css/app.css
```

## Security notes
- Browser sends only `question_id` + `option_id`. `is_correct` and `points_awarded` are read from the DB inside
  `QuizScoringService::submitAnswer` (extra fields are ignored; tested).
- Student HTML/JSON never includes `is_correct` (`Option::$hidden`, student queries don't select it).
- Checks per answer: student joined classroom (policy), quiz active, question ∈ quiz, option ∈ question,
  not already answered. DB `UNIQUE(student_id, question_id)` guards races; the quiz row is locked in the
  transaction so "close" and "answer" can't interleave. A page refresh just re-renders the next unanswered question.
- Faculty can only touch their own classrooms/quizzes (policies + `role` middleware); students only classrooms they joined.
- CSRF on all forms, `$fillable` on all models, Eloquent only (no raw user-supplied SQL), session regenerated on login.

## Design decisions (ambiguities resolved simply)
- One `users` table with `role`; registration lets people pick a role (open by design for the demo – restrict in production).
- Students log in, then enter code + display name. Display name is stored on `classroom_students.display_name`
  (extra column) and used on scoreboards.
- Re-joining a room you already joined is idempotent (redirects into the room) instead of an error.
- Each student answers questions in order, self-paced within the active round; "current question" = first unanswered.
- Correct answers are never shown to students; they see score, percentage and rank after closing.
- Scores are stored per answer at submission; "closing" freezes the round and the scoreboard is computed from stored rows
  (points DESC, then earlier last answer as tie-break). Only one active quiz per classroom.
- Questions can only be added/removed while a quiz is a draft.
- Sidebar has Dashboard / Classrooms / Results (quizzes live inside classrooms; no separate Profile page).
- Polling only; Reverb/Echo not used.
