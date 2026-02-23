<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reservation extends Model
{
    public const STATUS_AWAITING_PAYMENT = 'awaiting_payment';
    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_CANCELLED = 'cancelled';

    public const CANCELLED_BY_CUSTOMER = 'customer';
    public const CANCELLED_BY_ADMIN = 'admin';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'customer_id',
        'apartment_id',
        'status',
        'cancelled_by',
        'guests_count',
        'start_date',
        'end_date',
        'is_paid',
        'reminder_14_sent_at',
        'reminder_7_sent_at',
        'subtotal',
        'discount_percent',
        'total',
        'total_paid',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_paid' => 'boolean',
            'reminder_14_sent_at' => 'datetime',
            'reminder_7_sent_at' => 'datetime',
            'subtotal' => 'decimal:2',
            'discount_percent' => 'decimal:2',
            'total' => 'decimal:2',
            'total_paid' => 'decimal:2',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function apartment(): BelongsTo
    {
        return $this->belongsTo(Apartment::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
