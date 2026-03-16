<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Period extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'apartment_id',
        'name',
        'start_date',
        'end_date',
        'base_price',
        'extra_guest_price_2',
        'extra_guest_price_3',
        'extra_guest_price_4',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'base_price' => 'decimal:2',
            'extra_guest_price_2' => 'decimal:2',
            'extra_guest_price_3' => 'decimal:2',
            'extra_guest_price_4' => 'decimal:2',
        ];
    }

    public function apartment(): BelongsTo
    {
        return $this->belongsTo(Apartment::class);
    }
}
