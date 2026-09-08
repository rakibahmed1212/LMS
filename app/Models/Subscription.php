<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Subscription extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_TRIAL = 'trial';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_PAST_DUE = 'past_due';

    public const STATUS_PAUSED = 'paused';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_EXPIRED = 'expired';

    public const STATUS_BLOCKED = 'blocked';

    public const GRACE_DAYS = 3;

    protected $fillable = [
        'student_id', 'plan_id', 'coupon_id', 'status', 'started_at',
        'trial_ends_at', 'expires_at', 'grace_ends_at', 'cancelled_at',
        'auto_renew', 'billing_cycle', 'subscribed_price', 'currency',
        'dunning_stage', 'dunning_attempts', 'gateway',
        'gateway_subscription_id', 'metadata',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'expires_at' => 'datetime',
        'grace_ends_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'auto_renew' => 'boolean',
        'subscribed_price' => 'decimal:2',
        'metadata' => 'array',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /** Does the student currently have access through this subscription? */
    public function grantsAccess(): bool
    {
        return in_array($this->status, [self::STATUS_ACTIVE, self::STATUS_TRIAL], true)
            && $this->started_at !== null
            && $this->started_at->lte(now())
            && $this->expires_at !== null
            && $this->expires_at->isFuture();
    }

    /** Auto-renews another billing period and returns the new expires_at. */
    public function extendCycle(): Carbon
    {
        $base = $this->expires_at ?? $this->started_at;
        $next = $this->billing_cycle === SubscriptionPlan::CYCLE_ANNUAL
            ? $base->copy()->addYear()
            : $base->copy()->addMonth();

        return $next;
    }

    /**
     * Re-evaluate lifecycle status from date/dunning rules.
     * Returns true if status was changed.
     */
    public function reconcile(): bool
    {
        $previous = $this->status;

        if ($this->status === self::STATUS_CANCELLED || $this->status === self::STATUS_BLOCKED) {
            return false;
        }

        $now = now();

        // Grace period ended without recovery → cancelled.
        if ($this->grace_ends_at !== null && $now->gt($this->grace_ends_at)) {
            $this->status = self::STATUS_CANCELLED;
        }

        // Trial transitioned to active automatically.
        if ($this->status === self::STATUS_TRIAL && $this->trial_ends_at !== null && $now->gt($this->trial_ends_at) && $this->expires_at !== null) {
            $this->status = self::STATUS_ACTIVE;
        }

        // Fully expired after grace.
        if ($this->status === self::STATUS_EXPIRED && $this->grace_ends_at !== null && $now->gt($this->grace_ends_at)) {
            $this->status = self::STATUS_CANCELLED;
        }

        // Past-due not recovered after grace window.
        if ($this->status === self::STATUS_PAST_DUE && $this->grace_ends_at !== null && $now->gt($this->grace_ends_at)) {
            $this->status = self::STATUS_CANCELLED;
        }

        if ($this->status === self::STATUS_ACTIVE && $this->expires_at !== null && $now->gt($this->expires_at)) {
            $this->status = self::STATUS_EXPIRED;
            $this->grace_ends_at ??= $now->copy()->addDays(self::GRACE_DAYS);
        }

        if ($this->isDirty('status')) {
            $this->save();

            return $previous !== $this->status;
        }

        return false;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_ACTIVE, self::STATUS_TRIAL]);
    }

    public function scopeExpiringSoon(Builder $query, int $days = 7): Builder
    {
        return $query->whereIn('status', [self::STATUS_ACTIVE, self::STATUS_TRIAL])
            ->where('expires_at', '<=', now()->addDays($days));
    }

    public function scopeAssignedSubject(Builder $query, Subject $subject): Builder
    {
        return $query->whereHas('plan', function (Builder $q) use ($subject) {
            $q->whereHas('subjects', fn (Builder $s) => $s->whereKey($subject->id));
        });
    }
}
