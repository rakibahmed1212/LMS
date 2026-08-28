<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Subscription lifecycle: expiry, grace period, dunning, renewal attempts.
Schedule::command('subscriptions:reconcile')->dailyAt('09:00');

// Keep student watch/tracking and activity aggregates fresh (Phase 2 analytics).
// Schedule::command('progress:aggregate')->dailyAt('02:00');
