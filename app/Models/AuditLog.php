<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = ['user_id', 'action', 'entity_type', 'entity_id', 'old_data', 'new_data', 'ip_address', 'user_agent'];

    protected $casts = ['old_data' => 'array', 'new_data' => 'array'];

    public const CREATED_AT = 'created_at';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function record(string $action, ?Model $entity, array $changes = [], ?Model $actor = null): void
    {
        static::query()->create([
            'user_id' => $actor?->id,
            'action' => $action,
            'entity_type' => $entity?->getMorphClass(),
            'entity_id' => $entity?->getKey(),
            'old_data' => $changes['old'] ?? null,
            'new_data' => $changes['new'] ?? null,
            'ip_address' => request()->ip(),
            'user_agent' => substr((string) request()->userAgent(), 0, 255),
        ]);
    }
}
