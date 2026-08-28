<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Progress extends Model
{
    protected $fillable = [
        'student_id', 'lesson_id', 'watch_percent', 'watched_seconds',
        'last_position_seconds', 'completed', 'completed_at', 'last_watched_at',
    ];

    protected $casts = [
        'watch_percent' => 'integer',
        'watched_seconds' => 'integer',
        'last_position_seconds' => 'integer',
        'completed' => 'boolean',
        'completed_at' => 'datetime',
        'last_watched_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }
}
