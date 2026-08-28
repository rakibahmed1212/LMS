<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Question extends Model
{
    public const TYPE_MCQ = 'mcq';

    public const TYPE_TRUE_FALSE = 'true_false';

    public const TYPE_SHORT_ANSWER = 'short_answer';

    protected $fillable = [
        'quiz_id', 'type', 'text', 'options', 'correct_answer',
        'points', 'bank_tag', 'sort_order',
    ];

    protected $casts = [
        'options' => 'array',
        'points' => 'integer',
    ];

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    /**
     * Auto-mark objective answers.
     * Returns 0..points, or null when manual marking is required (short answer).
     */
    public function gradeAnswer(mixed $answer): ?float
    {
        if ($this->type === self::TYPE_SHORT_ANSWER) {
            return null; // tutor must mark manually
        }

        $correct = $this->correct_answer;

        if (is_array($correct)) {
            $correct = $correct[0] ?? null;
        }

        $normalize = fn ($v) => is_string($v) ? trim(strtolower($v)) : $v;

        return $normalize($answer) === $normalize($correct) ? (float) $this->points : 0.0;
    }
}
