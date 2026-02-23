<?php

use App\Jobs\CheckExternalAvailability;
use App\Models\Apartment;
use App\Models\BlockedDate;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\User;
use App\Models\UserGroup;
use App\Services\ExternalCalendar\ExternalCalendarSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

it('syncs and updates external calendar blocks by provider', function () {
    $apartment = createApartment([
        'airbnb_ical_url' => 'https://calendar.example/airbnb.ics',
    ]);

    Http::fake([
        'https://calendar.example/airbnb.ics' => Http::sequence()
            ->push(<<<ICS
BEGIN:VCALENDAR
BEGIN:VEVENT
UID:evt-1
DTSTART;VALUE=DATE:20260310
DTEND;VALUE=DATE:20260315
END:VEVENT
BEGIN:VEVENT
UID:evt-2
DTSTART;VALUE=DATE:20260320
DTEND;VALUE=DATE:20260322
END:VEVENT
END:VCALENDAR
ICS, 200)
            ->push(<<<ICS
BEGIN:VCALENDAR
BEGIN:VEVENT
UID:evt-1
DTSTART;VALUE=DATE:20260311
DTEND;VALUE=DATE:20260316
END:VEVENT
END:VCALENDAR
ICS, 200),
    ]);

    $first = app(ExternalCalendarSyncService::class)->syncApartment($apartment);

    expect($first['airbnb'])->toMatchArray([
        'added' => 2,
        'updated' => 0,
        'deleted' => 0,
        'error' => null,
    ]);

    expect(BlockedDate::query()->where('apartment_id', $apartment->id)->count())->toBe(2);

    $second = app(ExternalCalendarSyncService::class)->syncApartment($apartment);

    expect($second['airbnb'])->toMatchArray([
        'added' => 0,
        'updated' => 1,
        'deleted' => 1,
        'error' => null,
    ]);

    $remaining = BlockedDate::query()->where('apartment_id', $apartment->id)->get();

    expect($remaining)->toHaveCount(1)
        ->and($remaining->first()->start_date?->toDateString())->toBe('2026-03-11')
        ->and($remaining->first()->end_date?->toDateString())->toBe('2026-03-16');
});

it('cancels pending reservation when external calendar reports overlap', function () {
    config()->set('services.availability.mode', 'ics_sync');

    $customerGroup = UserGroup::query()->create([
        'name' => 'Customer',
        'slug' => UserGroup::CUSTOMER_SLUG,
    ]);

    $customer = User::query()->create([
        'name' => 'Mario',
        'surname' => 'Rossi',
        'email' => 'mario@example.com',
        'password' => 'password123',
        'user_group_id' => $customerGroup->id,
    ]);

    $apartment = createApartment([
        'airbnb_ical_url' => 'https://calendar.example/airbnb-overlap.ics',
    ]);

    $reservation = Reservation::query()->create([
        'customer_id' => $customer->id,
        'apartment_id' => $apartment->id,
        'status' => Reservation::STATUS_PENDING,
        'guests_count' => 2,
        'start_date' => '2026-04-10',
        'end_date' => '2026-04-14',
        'subtotal' => 100,
        'discount_percent' => 0,
        'total' => 100,
        'total_paid' => 100,
        'is_paid' => true,
    ]);

    Payment::query()->create([
        'reservation_id' => $reservation->id,
        'provider' => 'stripe',
        'step' => Payment::STEP_FULL,
        'status' => Payment::STATUS_PAID,
        'amount' => 100,
        'currency' => 'eur',
        'paid_at' => now(),
    ]);

    Http::fake([
        'https://calendar.example/airbnb-overlap.ics' => Http::response(<<<ICS
BEGIN:VCALENDAR
BEGIN:VEVENT
UID:conflict-1
DTSTART;VALUE=DATE:20260411
DTEND;VALUE=DATE:20260413
END:VEVENT
END:VCALENDAR
ICS, 200),
    ]);

    (new CheckExternalAvailability($reservation->id))->handle();
    expect($reservation->fresh()->status)->toBe(Reservation::STATUS_CANCELLED)
        ->and(
            BlockedDate::query()
                ->where('apartment_id', $apartment->id)
                ->whereDate('start_date', '2026-04-11')
                ->whereDate('end_date', '2026-04-13')
                ->exists()
        )->toBeTrue();
});

/**
 * @param array<string, mixed> $overrides
 */
function createApartment(array $overrides = []): Apartment
{
    return Apartment::query()->create(array_merge([
        'name_it' => 'Test Apartment',
        'name_en' => 'Test Apartment',
        'address_it' => 'Via Test 1',
        'address_en' => 'Test Street 1',
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
