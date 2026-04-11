<?php

use App\Models\Apartment;
use App\Models\Reservation;
use App\Models\User;
use App\Models\UserGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

it('exposes an ics feed with active site reservations for one apartment', function () {
    Carbon::setTestNow('2026-04-11');

    try {
        $customerGroup = UserGroup::query()->firstOrCreate(
            ['slug' => UserGroup::CUSTOMER_SLUG],
            ['name' => 'Customer'],
        );

        $customer = User::query()->create([
            'name' => 'Mario',
            'surname' => 'Rossi',
            'email' => 'mario@example.com',
            'phone' => '+39 333 1234567',
            'password' => 'password',
            'user_group_id' => $customerGroup->id,
            'preferred_locale' => 'it',
        ]);

        $apartment = createCalendarApartment([
            'name_it' => 'Feed Apartment',
        ]);

        $otherApartment = createCalendarApartment([
            'name_it' => 'Other Apartment',
        ]);

        $includedConfirmed = Reservation::query()->create([
            'customer_id' => $customer->id,
            'apartment_id' => $apartment->id,
            'status' => Reservation::STATUS_CONFIRMED,
            'guests_count' => 2,
            'start_date' => '2026-04-20',
            'end_date' => '2026-04-24',
            'subtotal' => 400,
            'discount_percent' => 0,
            'total' => 400,
            'total_paid' => 0,
        ]);

        $includedAwaiting = Reservation::query()->create([
            'customer_id' => $customer->id,
            'apartment_id' => $apartment->id,
            'status' => Reservation::STATUS_AWAITING_PAYMENT,
            'guests_count' => 1,
            'start_date' => '2026-05-01',
            'end_date' => '2026-05-03',
            'subtotal' => 200,
            'discount_percent' => 0,
            'total' => 200,
            'total_paid' => 0,
        ]);

        $excludedCancelled = Reservation::query()->create([
            'customer_id' => $customer->id,
            'apartment_id' => $apartment->id,
            'status' => Reservation::STATUS_CANCELLED,
            'guests_count' => 1,
            'start_date' => '2026-05-10',
            'end_date' => '2026-05-12',
            'subtotal' => 200,
            'discount_percent' => 0,
            'total' => 200,
            'total_paid' => 0,
        ]);

        $excludedPast = Reservation::query()->create([
            'customer_id' => $customer->id,
            'apartment_id' => $apartment->id,
            'status' => Reservation::STATUS_CONFIRMED,
            'guests_count' => 1,
            'start_date' => '2026-03-01',
            'end_date' => '2026-03-05',
            'subtotal' => 200,
            'discount_percent' => 0,
            'total' => 200,
            'total_paid' => 0,
        ]);

        $excludedOtherApartment = Reservation::query()->create([
            'customer_id' => $customer->id,
            'apartment_id' => $otherApartment->id,
            'status' => Reservation::STATUS_CONFIRMED,
            'guests_count' => 1,
            'start_date' => '2026-05-15',
            'end_date' => '2026-05-18',
            'subtotal' => 200,
            'discount_percent' => 0,
            'total' => 200,
            'total_paid' => 0,
        ]);

        $response = $this->get("/calendar/{$apartment->id}/reservations.ics");

        $response->assertOk();
        expect((string) $response->headers->get('Content-Type'))->toContain('text/calendar');

        $content = (string) $response->getContent();

        expect($content)
            ->toContain('BEGIN:VCALENDAR')
            ->toContain('END:VCALENDAR')
            ->toContain("UID:reservation-{$includedConfirmed->id}@")
            ->toContain("UID:reservation-{$includedAwaiting->id}@")
            ->toContain('DTSTART;VALUE=DATE:20260420')
            ->toContain('DTEND;VALUE=DATE:20260424')
            ->not->toContain("UID:reservation-{$excludedCancelled->id}@")
            ->not->toContain("UID:reservation-{$excludedPast->id}@")
            ->not->toContain("UID:reservation-{$excludedOtherApartment->id}@");
    } finally {
        Carbon::setTestNow();
    }
});

/**
 * @param array<string, mixed> $overrides
 */
function createCalendarApartment(array $overrides = []): Apartment
{
    return Apartment::query()->create(array_merge([
        'name_it' => 'Calendar Apartment',
        'name_en' => 'Calendar Apartment',
        'address_it' => 'Via Calendar 1',
        'address_en' => 'Calendar Street 1',
        'rooms_count' => 2,
        'beds_count' => 2,
        'bathrooms_count' => 1,
        'max_guests' => 4,
        'base_price' => 50,
        'extra_guest_price_2' => 10,
        'extra_guest_price_3' => 10,
        'extra_guest_price_4' => 10,
    ], $overrides));
}
