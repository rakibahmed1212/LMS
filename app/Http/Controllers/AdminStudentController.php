<?php

namespace App\Http\Controllers;

use App\Models\ClassYear;
use App\Models\Student;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminStudentController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'class_year_id' => ['nullable', 'integer', 'exists:class_years,id'],
            'subscription_status' => ['nullable', 'string', 'in:active,trial,past_due,expired,cancelled,none'],
        ]);

        $students = Student::query()
            ->with([
                'parent:id,name,email,phone',
                'classYears:id,name',
                'subscriptions.plan.subjects.classYear',
                'subscriptions.payments' => fn ($query) => $query->latest('paid_at')->latest(),
                'certificates.course',
            ])
            ->withCount([
                'progress as completed_lessons_count' => fn (Builder $query) => $query->where('completed', true),
                'progress as viewed_lessons_count',
                'certificates',
            ])
            ->when($filters['q'] ?? null, function (Builder $query, string $term) {
                $query->where(function (Builder $query) use ($term) {
                    $query->where('student_code', 'like', "%{$term}%")
                        ->orWhere('name', 'like', "%{$term}%")
                        ->orWhere('school', 'like', "%{$term}%")
                        ->orWhereHas('parent', function (Builder $query) use ($term) {
                            $query->where('name', 'like', "%{$term}%")
                                ->orWhere('email', 'like', "%{$term}%")
                                ->orWhere('phone', 'like', "%{$term}%");
                        });
                });
            })
            ->when($filters['class_year_id'] ?? null, fn (Builder $query, int $yearId) => $query
                ->whereHas('classYears', fn (Builder $query) => $query->whereKey($yearId)))
            ->when($filters['subscription_status'] ?? null, function (Builder $query, string $status) {
                if ($status === 'none') {
                    $query->whereDoesntHave('subscriptions');

                    return;
                }

                $query->whereHas('subscriptions', fn (Builder $query) => $query->where('status', $status));
            })
            ->latest()
            ->paginate(12)
            ->withQueryString()
            ->through(fn (Student $student) => [
                'id' => $student->id,
                'name' => $student->name,
                'student_code' => $student->student_code,
                'school' => $student->school,
                'is_active' => $student->is_active,
                'parent' => [
                    'name' => $student->parent?->name,
                    'email' => $student->parent?->email,
                    'phone' => $student->parent?->phone,
                ],
                'years' => $student->classYears->pluck('name'),
                'completed_lessons_count' => $student->completed_lessons_count,
                'viewed_lessons_count' => $student->viewed_lessons_count,
                'certificates_count' => $student->certificates_count,
                'subscriptions' => $student->subscriptions->map(fn (Subscription $subscription) => [
                    'id' => $subscription->id,
                    'status' => $subscription->status,
                    'plan' => $subscription->plan?->name,
                    'subjects' => $subscription->plan?->subjects
                        ->map(fn ($subject) => trim(($subject->classYear?->name ? $subject->classYear->name.' ' : '').$subject->name))
                        ->values() ?? [],
                    'billing_cycle' => $subscription->billing_cycle,
                    'price' => (float) $subscription->subscribed_price,
                    'currency' => $subscription->currency,
                    'expires_at' => $subscription->expires_at?->toDateString(),
                    'auto_renew' => $subscription->auto_renew,
                    'latest_payment' => optional($subscription->payments->first(), fn ($payment) => [
                        'invoice_no' => $payment->invoice_no,
                        'status' => $payment->status,
                        'total' => (float) $payment->total,
                        'paid_at' => $payment->paid_at?->toDateString(),
                    ]),
                ]),
            ]);

        return Inertia::render('Admin/Students', [
            'students' => $students,
            'filters' => [
                'q' => $filters['q'] ?? '',
                'class_year_id' => $filters['class_year_id'] ?? '',
                'subscription_status' => $filters['subscription_status'] ?? '',
            ],
            'classYears' => ClassYear::query()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'name']),
            'stats' => [
                'students' => Student::query()->count(),
                'active' => Student::query()->where('is_active', true)->count(),
                'subscribed' => Student::query()
                    ->whereHas('subscriptions', fn (Builder $query) => $query->whereIn('status', [
                        Subscription::STATUS_ACTIVE,
                        Subscription::STATUS_TRIAL,
                    ]))
                    ->count(),
                'past_due' => Student::query()
                    ->whereHas('subscriptions', fn (Builder $query) => $query->where('status', Subscription::STATUS_PAST_DUE))
                    ->count(),
            ],
        ]);
    }
}
