<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Response extends Model
{
    protected $fillable = [
        'quiz_id', 'question_id', 'student_id', 'option_id',
        'is_correct', 'points_awarded', 'answered_at',
    ];

    protected function casts(): array
    {
        return ['is_correct' => 'boolean', 'answered_at' => 'datetime'];
    }

    public function quiz(): BelongsTo     { return $this->belongsTo(Quiz::class); }
    public function question(): BelongsTo { return $this->belongsTo(Question::class); }
    public function option(): BelongsTo   { return $this->belongsTo(Option::class); }
    public function student(): BelongsTo  { return $this->belongsTo(User::class, 'student_id'); }
    public function user(): BelongsTo     { return $this->student(); }
}
