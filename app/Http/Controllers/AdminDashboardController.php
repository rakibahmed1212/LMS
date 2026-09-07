<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Gradebook;
use App\Models\Payment;
use App\Models\Student;
use App\Models\Subscription;
use App\Models\User;
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
            'total_users' => User::query()->count(),
            'active_subscriptions' => Subscription::query()->active()->count(),
            'expired_subscriptions' => Subscription::query()->where('status', 'expired')->count(),
            'past_due_subscriptions' => Subscription::query()->where('status', 'past_due')->count(),
            'revenue' => (float) $revenue,
            'paid_payments' => $paidCount,
            'renewals_next_7_days' => Subscription::query()->expiringSoon(7)->count(),
        ];

        $statusBreakdown = Subscription::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->orderBy('status')
            ->get()
            ->map(fn ($row) => [
                'status' => $row->status,
                'total' => (int) $row->total,
            ]);

        $renewals = Subscription::query()
            ->with(['student.parent', 'plan.subjects'])
            ->expiringSoon(14)
            ->orderBy('expires_at')
            ->limit(8)
            ->get()
            ->map(fn ($sub) => [
                'id' => $sub->id,
                'student' => $sub->student->name,
                'parent' => $sub->student->parent?->name,
                'plan' => $sub->plan->name,
                'status' => $sub->status,
                'expires_at' => $sub->expires_at?->toDateString(),
            ]);

        $revenueByPlan = Payment::query()
            ->where('payments.status', Payment::STATUS_PAID)
            ->join('subscriptions', 'payments.subscription_id', '=', 'subscriptions.id')
            ->join('subscription_plans', 'subscriptions.plan_id', '=', 'subscription_plans.id')
            ->selectRaw('subscription_plans.name as plan, sum(payments.total) as total')
            ->groupBy('subscription_plans.name')
            ->orderByDesc('total')
            ->limit(6)
            ->get()
            ->map(fn ($row) => [
                'plan' => $row->plan,
                'total' => (float) $row->total,
            ]);

        $atRiskStudents = Gradebook::query()
            ->with(['student.parent', 'subject.classYear'])
            ->whereNotNull('final_score')
            ->where('final_score', '<', 70)
            ->orderBy('final_score')
            ->limit(8)
            ->get()
            ->map(fn (Gradebook $grade) => [
                'id' => $grade->id,
                'student' => $grade->student->name,
                'parent' => $grade->student->parent?->name,
                'subject' => $grade->subject?->name,
                'year' => $grade->subject?->classYear?->name,
                'term' => $grade->term,
                'final_score' => (float) $grade->final_score,
            ]);

        $courseActivity = Course::query()
            ->with(['subject.classYear'])
            ->withCount(['lessons', 'certificates'])
            ->orderBy('title')
            ->get()
            ->map(fn (Course $course) => [
                'id' => $course->id,
                'title' => $course->title,
                'subject' => $course->subject?->name,
                'year' => $course->subject?->classYear?->name,
                'lessons_count' => $course->lessons_count,
                'certificates_count' => $course->certificates_count,
            ]);

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
            'status_breakdown' => $statusBreakdown,
            'renewals' => $renewals,
            'revenue_by_plan' => $revenueByPlan,
            'at_risk_students' => $atRiskStudents,
            'course_activity' => $courseActivity,
            'recent_subscriptions' => $recent,
        ]);
    }
}
