<?php

use App\Models\Apartment;
use App\Models\BlockedDate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

it('rejects requests without valid cron token', function () {
    config()->set('services.availability.cron_token', 'secret-token');

    $response = $this->getJson('/internal/cron/sync-ics');

    $response->assertStatus(401)
        ->assertJson([
            'ok' => false,
            'message' => 'Unauthorized.',
        ]);
});

it('runs sync through http endpoint with valid token', function () {
    config()->set('services.availability.cron_token', 'secret-token');

    $apartment = createCronApartment([
        'airbnb_ical_url' => 'https://calendar.example/airbnb-sync.ics',
    ]);

    Http::fake([
        'https://calendar.example/airbnb-sync.ics' => Http::response(<<<ICS
BEGIN:VCALENDAR
BEGIN:VEVENT
UID:http-sync-1
DTSTART;VALUE=DATE:20260510
DTEND;VALUE=DATE:20260513
END:VEVENT
END:VCALENDAR
ICS, 200),
    ]);

    $response = $this->getJson('/internal/cron/sync-ics?token=secret-token');

    $response->assertOk()
        ->assertJsonPath('ok', true)
        ->assertJsonPath('status', 'completed')
        ->assertJsonPath('summary.apartments', 1)
        ->assertJsonPath('summary.providers', 1)
        ->assertJsonPath('summary.errors', 0);

    expect(
        BlockedDate::query()
            ->where('apartment_id', $apartment->id)
            ->whereDate('start_date', '2026-05-10')
            ->whereDate('end_date', '2026-05-13')
            ->exists()
    )->toBeTrue();
});

it('returns already running when lock is busy', function () {
    config()->set('services.availability.cron_token', 'secret-token');

    $lock = Cache::lock('availability:sync-ics:http', 3300);
    $acquired = $lock->get();

    expect($acquired)->toBeTrue();

    try {
        $response = $this->getJson('/internal/cron/sync-ics?token=secret-token');

        $response->assertStatus(202)
            ->assertJson([
                'ok' => true,
                'status' => 'already_running',
            ]);
    } finally {
        $lock->release();
    }
});

/**
 * @param array<string, mixed> $overrides
 */
function createCronApartment(array $overrides = []): Apartment
{
    return Apartment::query()->create(array_merge([
        'name_it' => 'Cron Apartment',
        'name_en' => 'Cron Apartment',
        'address_it' => 'Via Cron 1',
        'address_en' => 'Cron Street 1',
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
