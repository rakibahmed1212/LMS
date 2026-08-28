<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assignment extends Model
{
    protected $fillable = [
        'lesson_id', 'title', 'description', 'deadline', 'max_score',
        'allow_resubmission', 'is_published',
    ];

    protected $casts = [
        'deadline' => 'datetime',
        'max_score' => 'decimal:2',
        'allow_resubmission' => 'boolean',
        'is_published' => 'boolean',
    ];

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class);
    }
}
