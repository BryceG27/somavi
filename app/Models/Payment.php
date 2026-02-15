<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    public const STEP_FULL = 'full';
    public const STEP_DEPOSIT = 'deposit';
    public const STEP_BALANCE = 'balance';

    public const STATUS_PENDING = 'pending';
    public const STATUS_AUTHORIZED = 'authorized';
    public const STATUS_PAID = 'paid';
    public const STATUS_REFUNDED = 'refunded';
    public const STATUS_VOIDED = 'voided';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'reservation_id',
        'provider',
        'step',
        'status',
        'amount',
        'currency',
        'due_at',
        'locale',
        'stripe_checkout_session_id',
        'stripe_payment_intent_id',
        'refund_amount',
        'paid_at',
        'refunded_at',
        'stripe_refund_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'refund_amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'refunded_at' => 'datetime',
            'due_at' => 'date',
        ];
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }
}
