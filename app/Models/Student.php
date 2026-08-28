<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id', 'student_code', 'name', 'dob', 'school', 'gender', 'is_active',
    ];

    protected $casts = [
        'dob' => 'date',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Student $student) {
            $student->student_code ??= self::generateStudentCode();
        });
    }

    public static function generateStudentCode(): string
    {
        do {
            $code = 'STU-'.date('Y').'-'.Str::padLeft((string) mt_rand(1, 99999), 5, '0');
        } while (static::query()->where('student_code', $code)->exists());

        return $code;
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(StudentEnrollment::class);
    }

    public function classYears()
    {
        return $this->belongsToMany(ClassYear::class, 'student_enrollments')
            ->withPivot('academic_session_id');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function progress(): HasMany
    {
        return $this->hasMany(Progress::class);
    }

    public function quizAttempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class);
    }

    public function gradebook(): HasMany
    {
        return $this->hasMany(Gradebook::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    public function discussions(): HasMany
    {
        return $this->hasMany(DiscussionThread::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function activeSubscriptions(): HasMany
    {
        return $this->subscriptions()
            ->whereIn('status', [Subscription::STATUS_ACTIVE, Subscription::STATUS_TRIAL, Subscription::STATUS_PAST_DUE]);
    }
}
