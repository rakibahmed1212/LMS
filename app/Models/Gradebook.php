<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Gradebook extends Model
{
    protected $fillable = [
        'student_id', 'subject_id', 'quiz_weight', 'assignment_weight',
        'quiz_avg', 'assignment_avg', 'final_score', 'term', 'computed_at',
    ];

    protected $casts = [
        'quiz_weight' => 'decimal:2',
        'assignment_weight' => 'decimal:2',
        'quiz_avg' => 'decimal:2',
        'assignment_avg' => 'decimal:2',
        'final_score' => 'decimal:2',
        'computed_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /** Weighted final score when quiz + assignment weights sum to 100. */
    public function recalc(float $quizAvg, float $assignmentAvg): void
    {
        $this->quiz_avg = round($quizAvg, 2);
        $this->assignment_avg = round($assignmentAvg, 2);

        $totalWeight = $this->quiz_weight + $this->assignment_weight;
        $this->final_score = $totalWeight > 0
            ? round(($quizAvg * $this->quiz_weight + $assignmentAvg * $this->assignment_weight) / $totalWeight, 2)
            : 0;

        $this->computed_at = now();
        $this->save();
    }
}
