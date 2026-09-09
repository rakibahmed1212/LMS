<?php

namespace App\Http\Controllers;

use App\Models\ClassYear;
use App\Models\Course;
use App\Models\Student;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\AccessControlService;
use App\Services\ProgressService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ParentDashboardController extends Controller
{
    public function index(Request $request, AccessControlService $access, ProgressService $progress)
    {
        /** @var User $user */
        $user = $request->user();

        $children = $user->students()
            ->with([
                'subscriptions.plan.subjects',
                'subscriptions.payments',
                'gradebook.subject',
                'certificates.course.subject.classYear',
                'classYears',
                'progress',
            ])
            ->get()
            ->map(function (Student $student) use ($access, $progress) {
                $subjects = $access->accessibleSubjects($student);
                $courses = Course::query()
                    ->whereIn('subject_id', $subjects->pluck('id'))
                    ->with('subject.classYear')
                    ->get()
                    ->map(fn (Course $course) => [
                        'id' => $course->id,
                        'title' => $course->title,
                        'subject' => $course->subject?->name,
                        'year' => $course->subject?->classYear?->name,
                        'progress' => $progress->courseProgress($student, $course),
                    ]);

                return [
                    'id' => $student->id,
                    'name' => $student->name,
                    'preferred_name' => $student->preferred_name,
                    'student_code' => $student->student_code,
                    'school' => $student->school,
                    'city' => $student->city,
                    'postcode' => $student->postcode,
                    'country' => $student->country,
                    'emergency_contact_name' => $student->emergency_contact_name,
                    'emergency_contact_phone' => $student->emergency_contact_phone,
                    'learning_needs' => $student->learning_needs,
                    'medical_notes' => $student->medical_notes,
                    'years' => $student->classYears->map(fn ($y) => $y->name),
                    'accessible_subjects' => $subjects->pluck('name'),
                    'subscriptions' => $student->subscriptions->map(function ($sub) {
                        return [
                            'id' => $sub->id,
                            'plan' => $sub->plan->name,
                            'status' => $sub->status,
                            'billing_cycle' => $sub->billing_cycle,
                            'price' => $sub->subscribed_price,
                            'expires_at' => $sub->expires_at?->toDateString(),
                            'renewal_due' => $sub->expires_at?->lt(now()->addDays(7)),
                            'payments' => $sub->payments->map(fn ($payment) => [
                                'invoice_no' => $payment->invoice_no,
                                'status' => $payment->status,
                                'total' => (float) $payment->total,
                                'paid_at' => $payment->paid_at?->toDateString(),
                            ]),
                        ];
                    }),
                    'courses' => $courses,
                    'grades' => $student->gradebook->map(fn ($grade) => [
                        'subject' => $grade->subject?->name,
                        'term' => $grade->term,
                        'quiz_avg' => $grade->quiz_avg ? (float) $grade->quiz_avg : null,
                        'assignment_avg' => $grade->assignment_avg ? (float) $grade->assignment_avg : null,
                        'final_score' => $grade->final_score ? (float) $grade->final_score : null,
                    ]),
                    'certificates' => $student->certificates->map(fn ($certificate) => [
                        'code' => $certificate->cert_code,
                        'course' => $certificate->course?->title,
                        'issued_at' => $certificate->issued_at?->toDateString(),
                        'verify_url' => route('certificates.verify', ['code' => $certificate->cert_code]),
                    ]),
                ];
            });

        return Inertia::render('Parent/Dashboard', [
            'children' => $children,
            'hasSubscriptionPlans' => SubscriptionPlan::query()->where('is_active', true)->exists(),
            'classYears' => ClassYear::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }
}
