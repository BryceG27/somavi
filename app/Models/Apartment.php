<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Apartment extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name_it',
        'name_en',
        'address_it',
        'address_en',
        'hero_kicker_it',
        'hero_kicker_en',
        'hero_headline_it',
        'hero_headline_en',
        'hero_body_it',
        'hero_body_en',
        'hero_primary_cta_it',
        'hero_primary_cta_en',
        'hero_secondary_cta_it',
        'hero_secondary_cta_en',
        'rooms_count',
        'beds_count',
        'bathrooms_count',
        'max_guests',
        'check_in_text',
        'check_out_text',
        'description_it',
        'description_en',
        'contact_email',
        'contact_phone',
        'whatsapp_url',
        'airbnb_url',
        'booking_url',
        'airbnb_api_key',
        'booking_api_key',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'airbnb_api_key' => 'encrypted',
        'booking_api_key' => 'encrypted',
    ];

    public function attachments(): HasMany
    {
        return $this->hasMany(ApartmentAttachment::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }
}
