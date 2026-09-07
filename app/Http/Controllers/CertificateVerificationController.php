<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use Inertia\Inertia;

class CertificateVerificationController extends Controller
{
    public function show(string $code)
    {
        $certificate = Certificate::query()
            ->where('cert_code', $code)
            ->with(['student', 'course.subject.classYear'])
            ->first();

        return Inertia::render('Certificates/Verify', [
            'certificate' => $certificate ? [
                'code' => $certificate->cert_code,
                'issued_at' => $certificate->issued_at?->toFormattedDateString(),
                'student' => [
                    'name' => $certificate->student->name,
                    'student_code' => $certificate->student->student_code,
                ],
                'course' => [
                    'title' => $certificate->course->title,
                    'subject' => $certificate->course->subject?->name,
                    'year' => $certificate->course->subject?->classYear?->name,
                ],
            ] : null,
            'code' => $code,
        ]);
    }
}
