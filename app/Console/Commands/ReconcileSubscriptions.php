<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Models\Subscription;
use App\Services\SubscriptionService;
use Illuminate\Console\Command;

class ReconcileSubscriptions extends Command
{
    protected $signature = 'subscriptions:reconcile';

    protected $description = 'Reconcile subscription statuses (expiry, grace, dunning, trials)';

    public function handle(SubscriptionService $service): int
    {
        $changed = 0;
        $expiring = 0;

        Subscription::query()
            ->whereNotIn('status', [Subscription::STATUS_CANCELLED, Subscription::STATUS_BLOCKED])
            ->with('plan')
            ->orderBy('id')
            ->chunkById(200, function ($subscriptions) use ($service, &$changed, &$expiring) {
                foreach ($subscriptions as $subscription) {
                    $before = $subscription->status;

                    if ($subscription->reconcile()) {
                        $changed++;
                        $this->line(sprintf(
                            ' #%s: %s → %s',
                            $subscription->id,
                            $before,
                            $subscription->status,
                        ));
                    }

                    // Renewing: charge the next cycle and roll forward.
                    if ($subscription->auto_renew
                        && $subscription->expires_at !== null
                        && $subscription->expires_at->subDay()->lte(now())
                        && in_array($subscription->status, [Subscription::STATUS_ACTIVE, Subscription::STATUS_TRIAL], true)) {
                        $service->markRenewalFailed($subscription, $this->newPayment($subscription));
                        $expiring++;
                        $this->line(sprintf(' #%s: renewal attempt → %s', $subscription->id, $subscription->status));
                    }
                }
            });

        $this->info("Reconciled. Status changes: {$changed}, renewal attempts: {$expiring}.");

        return self::SUCCESS;
    }

    private function newPayment(Subscription $subscription)
    {
        return $subscription->payments()->create([
            'invoice_no' => Payment::generateInvoiceNo(),
            'amount' => $subscription->subscribed_price,
            'discount_amount' => 0,
            'total' => $subscription->subscribed_price,
            'currency' => $subscription->currency,
            'status' => 'pending',
            'gateway' => $subscription->gateway,
        ]);
    }
}
