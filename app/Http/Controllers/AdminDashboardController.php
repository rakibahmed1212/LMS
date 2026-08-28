<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Student;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        $revenue = Payment::query()->where('status', Payment::STATUS_PAID)->sum('total');
        $paidCount = Payment::query()->where('status', Payment::STATUS_PAID)->count();

        $overview = [
            'total_students' => Student::query()->count(),
            'active_subscriptions' => Subscription::query()->active()->count(),
            'expired_subscriptions' => Subscription::query()->where('status', 'expired')->count(),
            'past_due_subscriptions' => Subscription::query()->where('status', 'past_due')->count(),
            'revenue' => (float) $revenue,
            'paid_payments' => $paidCount,
            'renewals_next_7_days' => Subscription::query()->expiringSoon(7)->count(),
        ];

        $recent = Subscription::query()
            ->with(['student.parent', 'plan.subjects'])
            ->latest('updated_at')
            ->limit(10)
            ->get()
            ->map(fn ($sub) => [
                'id' => $sub->id,
                'student' => $sub->student->name,
                'parent' => $sub->student->parent?->name,
                'plan' => $sub->plan->name,
                'covered_subjects' => $sub->plan->subjects->pluck('name'),
                'status' => $sub->status,
                'expires_at' => $sub->expires_at?->toDateString(),
            ]);

        return Inertia::render('Admin/Dashboard', [
            'overview' => $overview,
            'recent_subscriptions' => $recent,
        ]);
    }
}
