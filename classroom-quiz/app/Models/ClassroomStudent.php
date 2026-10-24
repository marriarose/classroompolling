<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ClassroomStudent extends Pivot
{
    protected $table = 'classroom_students';

    public $incrementing = true;

    protected $fillable = ['classroom_id', 'student_id', 'display_name', 'joined_at'];

    protected function casts(): array
    {
        return ['joined_at' => 'datetime'];
    }
}
