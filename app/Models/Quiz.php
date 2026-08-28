<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quiz extends Model
{
    public const TYPE_PRACTICE = 'practice';

    public const TYPE_GRADED = 'graded';

    public const TYPE_END_OF_TOPIC = 'end_of_topic';

    public const TYPE_END_OF_YEAR = 'end_of_year';

    protected $fillable = [
        'lesson_id', 'title', 'type', 'time_limit_minutes', 'max_score',
        'shuffle_questions', 'is_published', 'settings',
    ];

    protected $casts = [
        'time_limit_minutes' => 'integer',
        'max_score' => 'integer',
        'shuffle_questions' => 'boolean',
        'is_published' => 'boolean',
        'settings' => 'array',
    ];

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('sort_order');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    /** Best score among a student's attempts, for gradebook rollups. */
    public function bestScoreFor(Student $student): ?float
    {
        return $this->attempts()
            ->where('student_id', $student->id)
            ->whereNotNull('score')
            ->max('score');
    }
}
