<?php

namespace Database\Seeders;

use App\Models\Apartment;
use App\Models\Period;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ApartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $apartment = Apartment::firstOrCreate(
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
                'airbnb_ical_url' => "https://www.airbnb.it/calendar/ical/47575544.ics?s=4d6c5c6077a9401f30ce9bf76737c33c",
                'booking_url' => 'https://www.booking.com',
                'booking_ical_url' => "https://ical.booking.com/v1/export?t=761a977a-506d-4962-ba8e-e121532af433",
                'vrbo_url' => 'https://www.vrbo.com',
                'vrbo_ical_url' => "http://www.vrbo.com/icalendar/000b98aa65794086a1bbb9a495bb4db1.ics?nonTentative",
                'base_price' => 40,
                'extra_guest_price_2' => 35,
                'extra_guest_price_3' => 30,
                'extra_guest_price_4' => 25,
            ]
        );

        $year = Carbon::now()->year;

        Period::updateOrCreate(
            [
                'apartment_id' => $apartment->id,
                'name' => 'Primavera Promo',
            ],
            [
                'start_date' => Carbon::create($year, 4, 1)->toDateString(),
                'end_date' => Carbon::create($year, 6, 1)->toDateString(),
                'base_price' => 55,
                'extra_guest_price_2' => 38,
                'extra_guest_price_3' => 32,
                'extra_guest_price_4' => 28,
                'discount_percentage' => 10,
            ],
        );

        Period::updateOrCreate(
            [
                'apartment_id' => $apartment->id,
                'name' => 'Alta Stagione',
            ],
            [
                'start_date' => Carbon::create($year, 7, 1)->toDateString(),
                'end_date' => Carbon::create($year, 9, 1)->toDateString(),
                'base_price' => 85,
                'extra_guest_price_2' => 50,
                'extra_guest_price_3' => 45,
                'extra_guest_price_4' => 38,
                'discount_percentage' => 5,
            ],
        );

        Period::updateOrCreate(
            [
                'apartment_id' => $apartment->id,
                'name' => 'Autunno Smart',
            ],
            [
                'start_date' => Carbon::create($year, 10, 1)->toDateString(),
                'end_date' => Carbon::create($year, 12, 1)->toDateString(),
                'base_price' => 50,
                'extra_guest_price_2' => 35,
                'extra_guest_price_3' => 30,
                'extra_guest_price_4' => 25,
                'discount_percentage' => 12,
            ],
        );
    }
}
