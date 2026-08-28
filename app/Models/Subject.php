<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    protected $fillable = ['class_year_id', 'name', 'slug', 'color', 'is_active', 'sort_order'];

    protected $attributes = [
        'is_active' => true,
        'color' => '#4f46e5',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function classYear(): BelongsTo
    {
        return $this->belongsTo(ClassYear::class);
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    public function plans(): HasMany
    {
        return $this->hasMany(SubscriptionPlan::class);
    }

    public function gradebooks(): HasMany
    {
        return $this->hasMany(Gradebook::class);
    }
}
