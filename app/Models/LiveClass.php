<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiveClass extends Model
{
    protected $fillable = [
        'subject_id', 'course_id', 'tutor_id', 'title', 'provider',
        'meeting_url', 'scheduled_at', 'duration_minutes', 'is_active',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function tutor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tutor_id');
    }

    public function hasEnded(): bool
    {
        return $this->scheduled_at->addMinutes($this->duration_minutes)->lt(now());
    }
}
