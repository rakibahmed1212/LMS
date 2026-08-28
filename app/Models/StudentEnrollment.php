<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentEnrollment extends Model
{
    protected $fillable = ['student_id', 'class_year_id', 'academic_session_id'];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function classYear(): BelongsTo
    {
        return $this->belongsTo(ClassYear::class);
    }

    public function academicSession(): BelongsTo
    {
        return $this->belongsTo(AcademicSession::class);
    }
}
