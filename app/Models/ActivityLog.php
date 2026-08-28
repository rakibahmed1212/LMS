<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    public const ACTION_LESSON_VIEWED = 'lesson_viewed';

    public const ACTION_VIDEO_WATCHED = 'video_watched';

    public const ACTION_QUIZ_TAKEN = 'quiz_taken';

    public const ACTION_QUIZ_PASSED = 'quiz_passed';

    public const ACTION_ASSIGNMENT_SUBMITTED = 'assignment_submitted';

    public const ACTION_CERTIFICATE_ISSUED = 'certificate_issued';

    public const ACTION_LOGIN = 'login';

    public $timestamps = false;

    protected $fillable = ['student_id', 'lesson_id', 'action', 'seconds_spent', 'occurred_at'];

    protected $casts = ['occurred_at' => 'datetime'];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }
}
