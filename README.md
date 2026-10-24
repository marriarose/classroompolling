# ClassQuiz – Classroom Polling & Quiz System

The project has been configured and is actively running on:
**[http://127.0.0.1:8000](http://127.0.0.1:8000)**

---

## Quick Start / Running the App

The server is currently running in the background. If you restart your machine or need to start it again in the future:
```powershell
.\run.bat
```
or
```powershell
cd classroom-quiz
php artisan serve
```

## Running Tests
All 17 feature & unit test suites are passing:
```powershell
.\test.bat
```
or
```powershell
cd classroom-quiz
php artisan test
```

---

## Seeded Demo Accounts

| Role | Email | Password |
|---|---|---|
| **Faculty** | `faculty@example.com` | `password` |
| **Student** | `student@example.com` | `password` |

Other seeded students:
- `anu@example.com` (password: `password`)
- `maria@example.com` (password: `password`)
- `rahul@example.com` (password: `password`)
- `neha@example.com` (password: `password`)

---

## Pre-configured Classrooms
- **AI Fundamentals (`DEMK42`)**: Includes a closed quiz with sample results & rankings.
- **Machine Learning (`MLRN26`)**: Includes a draft quiz ready for faculty to start.
