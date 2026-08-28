<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    public const CYCLE_MONTHLY = 'monthly';

    public const CYCLE_ANNUAL = 'annual';

    protected $fillable = [
        'subject_id', 'name', 'billing_cycle', 'price', 'currency',
        'title_internal', 'trial_days', 'is_bundle', 'is_active',
    ];

    protected $attributes = [
        'currency' => 'GBP',
        'is_bundle' => false,
        'is_active' => true,
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_bundle' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /** Subjects covered by this plan (single = one, bundle = many). */
    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'subscription_plan_subject');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function monthlyAnnualSibling(): BelongsTo
    {
        return $this->belongsTo(self::class, 'id')
            ->where('subject_id', $this->subject_id);
    }
}
