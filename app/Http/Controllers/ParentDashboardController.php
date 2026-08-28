<?php

namespace App\Http\Controllers;

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
                'classYears',
                'progress',
            ])
            ->get()
            ->map(function (Student $student) use ($access) {
                $subjects = $access->accessibleSubjects($student);

                return [
                    'id' => $student->id,
                    'name' => $student->name,
                    'student_code' => $student->student_code,
                    'school' => $student->school,
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
                        ];
                    }),
                    'progress' => $student->progress->map(fn ($p) => [
                        'lesson_id' => $p->lesson_id,
                        'watch_percent' => $p->watch_percent,
                        'completed' => $p->completed,
                    ]),
                ];
            });

        return Inertia::render('Parent/Dashboard', [
            'children' => $children,
            'hasSubscriptionPlans' => SubscriptionPlan::query()->where('is_active', true)->exists(),
        ]);
    }
}
