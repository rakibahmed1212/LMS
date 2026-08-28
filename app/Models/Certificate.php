<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Certificate extends Model
{
    protected $fillable = ['student_id', 'course_id', 'cert_code', 'template', 'pdf_url', 'qr_url', 'issued_at'];

    protected $casts = ['issued_at' => 'datetime'];

    protected static function booted(): void
    {
        static::creating(function (Certificate $certificate) {
            $certificate->issued_at ??= now();
            $certificate->cert_code ??= static::generateCode();
        });
    }

    public static function generateCode(): string
    {
        return 'CERT-'.date('Y').'-'.strtoupper(Str::random(10));
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
