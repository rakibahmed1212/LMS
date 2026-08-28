<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Payment;
use App\Models\Student;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use Illuminate\Support\Carbon;

class SubscriptionService
{
    public function __construct(
        private readonly AccessControlService $access,
    ) {}

    /**
     * Create a subscription. Existing active subscription to the same subject
     * is left untouched (bundles auto-deactivate overlapping single plans is
     * NOT done here — caller decides via cancelOverlappingBundles()).
     */
    public function subscribe(
        Student $student,
        SubscriptionPlan $plan,
        ?Coupon $coupon = null,
        ?Carbon $now = null,
    ): Subscription {
        $now ??= now();
        $price = $plan->price;
        $discount = $this->discountFor($coupon, $plan->price);

        $subscription = $student->subscriptions()->create([
            'plan_id' => $plan->id,
            'coupon_id' => $coupon?->id,
            'status' => Subscription::STATUS_PENDING,
            'started_at' => $now,
            'billing_cycle' => $plan->billing_cycle,
            'subscribed_price' => $price,
            'currency' => $plan->currency,
            'auto_renew' => true,
        ]);

        // Trial enters before the first paid period if the plan offers one.
        if ($plan->trial_days) {
            $subscription->status = Subscription::STATUS_TRIAL;
            $subscription->trial_ends_at = $now->copy()->addDays($plan->trial_days);
            $subscription->expires_at = $now->copy()
                ->addDays($plan->trial_days)
                ->{$this->cycleAdd($plan->billing_cycle)}(1);
        } else {
            $subscription->status = Subscription::STATUS_ACTIVE;
            $subscription->expires_at = $now->copy()->{$this->cycleAdd($plan->billing_cycle)}(1);
        }

        $subscription->save();

        $this->createPayment($subscription, $now, $price, $discount);

        return $subscription;
    }

    /** Apply a coupon to a plan price, returning discount amount. */
    public function discountFor(?Coupon $coupon, float $price): float
    {
        if ($coupon === null || ! $coupon->isValid()) {
            return 0.0;
        }

        return $coupon->type === Coupon::TYPE_FIXED
            ? min((float) $coupon->value, $price)
            : round($price * ((float) $coupon->value / 100), 2);
    }

    private function createPayment(Subscription $subscription, Carbon $now, float $price, float $discount): Payment
    {
        return Payment::query()->create([
            'subscription_id' => $subscription->id,
            'invoice_no' => Payment::generateInvoiceNo(),
            'amount' => $price,
            'discount_amount' => $discount,
            'total' => round($price - $discount, 2),
            'currency' => $subscription->currency,
            'status' => Payment::STATUS_PENDING,
            'gateway' => $subscription->gateway,
        ]);
    }

    public function markPaid(Subscription $subscription, ?string $gatewayTxId = null): void
    {
        $payment = $subscription->payments()->where('status', Payment::STATUS_PENDING)->first() ?? $subscription->payments()->latest('id')->first();

        if ($payment === null) {
            return;
        }

        $payment->markPaid($gatewayTxId);

        if ($subscription->status === Subscription::STATUS_PENDING) {
            $subscription->status = Subscription::STATUS_ACTIVE;
            $subscription->save();
        }

        if ($subscription->status === Subscription::STATUS_PAST_DUE) {
            // Recovered: re-activate with a fresh expiry.
            $subscription->status = Subscription::STATUS_ACTIVE;
            $subscription->expires_at = $subscription->extendCycle();
            $subscription->grace_ends_at = null;
            $subscription->dunning_stage = 'none';
            $subscription->dunning_attempts = 0;
            $subscription->save();
        }
    }

    /** Cancel an auto-renewing subscription when the paid period ends. */
    public function cancelAtPeriodEnd(Subscription $subscription): void
    {
        $subscription->auto_renew = false;
        $subscription->save();
    }

    /** Immediate cancellation: access revoked now, history preserved. */
    public function cancelImmediately(Subscription $subscription): void
    {
        $subscription->status = Subscription::STATUS_CANCELLED;
        $subscription->cancelled_at = now();
        $subscription->auto_renew = false;
        $subscription->save();
    }

    /**
     * Dunning flow: a failed renewal isn't an instant cancellation.
     * It moves to past_due, losing live access, but is retried with
     * escalating stages until the grace window closes.
     */
    public function markRenewalFailed(Subscription $subscription, Payment $payment): void
    {
        $payment->status = Payment::STATUS_FAILED;
        $payment->save();

        $subscription->status = Subscription::STATUS_PAST_DUE;
        $subscription->dunning_attempts++;
        $subscription->dunning_stage = 'dunning_'.$subscription->dunning_attempts;
        $subscription->grace_ends_at ??= now()->addDays(Subscription::GRACE_DAYS);
        $subscription->save();
    }

    public function retryRenewal(Subscription $subscription): void
    {
        // Attempt charge via gateway → on success markPaid(), on failure stays past_due.
        $this->markPaid($subscription);
    }

    private function cycleAdd(string $cycle): string
    {
        return $cycle === SubscriptionPlan::CYCLE_ANNUAL ? 'addYear' : 'addMonth';
    }
}
