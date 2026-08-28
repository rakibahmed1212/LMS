<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Payment extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_PAID = 'paid';

    public const STATUS_FAILED = 'failed';

    public const STATUS_REFUNDED = 'refunded';

    protected $fillable = [
        'subscription_id', 'invoice_no', 'amount', 'discount_amount', 'total',
        'currency', 'status', 'gateway', 'gateway_transaction_id', 'paid_at', 'payload',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_at' => 'datetime',
        'payload' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (Payment $payment) {
            $payment->invoice_no ??= static::generateInvoiceNo();
        });
    }

    public static function generateInvoiceNo(): string
    {
        return 'INV-'.date('Y').'-'.strtoupper(Str::random(8));
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function markPaid(?string $txId = null, array $payload = []): void
    {
        $this->status = self::STATUS_PAID;
        $this->paid_at = now();
        $this->gateway_transaction_id = $txId ?? $this->gateway_transaction_id;
        $this->payload = array_merge($this->payload ?? [], $payload);
        $this->save();
    }
}
