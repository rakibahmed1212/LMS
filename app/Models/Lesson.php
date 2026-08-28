<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lesson extends Model
{
    protected $fillable = [
        'module_id', 'title', 'notes', 'sort_order', 'video_provider', 'video_id',
        'video_thumbnail', 'subtitle_url', 'duration_seconds', 'is_published', 'is_free',
    ];

    protected $casts = [
        'duration_seconds' => 'integer',
        'is_published' => 'boolean',
        'is_free' => 'boolean',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function worksheets(): HasMany
    {
        return $this->hasMany(Worksheet::class)->orderBy('sort_order');
    }

    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    public function discussions(): HasMany
    {
        return $this->hasMany(DiscussionThread::class);
    }

    public function progress(): HasMany
    {
        return $this->hasMany(Progress::class);
    }
}
