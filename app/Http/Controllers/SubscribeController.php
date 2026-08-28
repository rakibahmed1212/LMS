<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Student;
use App\Models\SubscriptionPlan;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SubscribeController extends Controller
{
    public function show(Request $request, Student $student)
    {
        $this->authorizeParent($request, $student);

        $plans = SubscriptionPlan::query()
            ->where('is_active', true)
            ->with('subject')
            ->get()
            ->map(fn ($plan) => [
                'id' => $plan->id,
                'name' => $plan->name,
                'billing_cycle' => $plan->billing_cycle,
                'price' => (float) $plan->price,
                'trial_days' => $plan->trial_days,
                'is_bundle' => $plan->is_bundle,
                'subject' => $plan->subject?->name,
                'subscribed' => $student->subscriptions()
                    ->where('plan_id', $plan->id)
                    ->whereIn('status', ['active', 'trial'])
                    ->exists(),
            ]);

        return Inertia::render('Subscriptions/Purchase', [
            'student' => [
                'id' => $student->id,
                'name' => $student->name,
                'student_code' => $student->student_code,
            ],
            'plans' => $plans,
        ]);
    }

    public function store(Request $request, Student $student, SubscriptionService $service)
    {
        $this->authorizeParent($request, $student);

        $validated = $request->validate([
            'plan_id' => ['required', 'exists:subscription_plans,id'],
            'coupon_code' => ['nullable', 'string', 'max:50'],
        ]);

        $plan = SubscriptionPlan::findOrFail($validated['plan_id']);

        $coupon = null;
        if (! empty($validated['coupon_code'])) {
            $coupon = Coupon::where('code', $validated['coupon_code'])->first();
            if ($coupon === null || ! $coupon->isValid()) {
                return back()->withErrors(['coupon_code' => 'Invalid or expired coupon code.']);
            }
        }

        // Gateway flow (Stripe/SSLCommerz tokenization) happens in Phase 2;
        // v1 simulates a successful charge via markPaid().
        $subscription = $service->subscribe($student, $plan, $coupon);
        $service->markPaid($subscription, 'v1-simulated-tx');

        return redirect()
            ->route('parent.dashboard')
            ->with('success', 'Subscription activated for '.$plan->name.'.');
    }

    private function authorizeParent(Request $request, Student $student): void
    {
        abort_if($student->parent_id !== $request->user()->id && ! $request->user()->hasRole('super_admin'), 403);
    }
}
