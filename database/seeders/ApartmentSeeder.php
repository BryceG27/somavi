<?php

namespace Database\Seeders;

use App\Models\Apartment;
use Illuminate\Database\Seeder;

class ApartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Apartment::firstOrCreate(
            ['name_it' => 'La Kalsa di Somavi'],
            [
                'name_en' => 'La Kalsa di Somavi',
                'address_it' => 'Via dello Spasimo, 37, 90133 Palermo, PA',
                'address_en' => 'Via dello Spasimo, 37, 90133 Palermo, PA',
                'hero_kicker_it' => 'La tua casa privata a Palermo',
                'hero_kicker_en' => 'Your private home in Palermo',
                'hero_headline_it' => 'Vivi la citta con lentezza, comfort e luce mediterranea.',
                'hero_headline_en' => 'Live the city with calm, comfort, and Mediterranean light.',
                'hero_body_it' => 'Un soggiorno essenziale e luminoso, pensato per chi ama muoversi a piedi e respirare la citta.',
                'hero_body_en' => 'A bright, essential stay designed for those who love to walk and breathe the city.',
                'hero_primary_cta_it' => 'Scopri la casa',
                'hero_primary_cta_en' => 'Discover the apartment',
                'hero_secondary_cta_it' => 'Verifica disponibilita',
                'hero_secondary_cta_en' => 'Check availability',
                'rooms_count' => 2,
                'beds_count' => 3,
                'bathrooms_count' => 1,
                'max_guests' => 4,
                'check_in_text' => 'Check-in dalle 15:00',
                'check_out_text' => 'Check-out entro le 11:00',
                'description_it' => 'Appartamento accogliente con spazi luminosi e atmosfera autentica nel cuore della Kalsa.',
                'description_en' => 'Cozy apartment with bright spaces and an authentic atmosphere in the heart of Kalsa.',
                'contact_email' => null,
                'contact_phone' => null,
                'whatsapp_url' => null,
                'airbnb_url' => 'https://www.airbnb.com',
                'airbnb_ical_url' => null,
                'booking_url' => 'https://www.booking.com',
                'booking_ical_url' => null,
                'vrbo_url' => 'https://www.vrbo.com',
                'vrbo_ical_url' => null,
                'base_price' => 40,
                'extra_guest_price_2' => 35,
                'extra_guest_price_3' => 30,
                'extra_guest_price_4' => 25,
            ]
        );
    }
}
