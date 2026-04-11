<?php

use App\Models\Apartment;
use App\Models\Period;
use App\Models\Reservation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

it('uses apartment pricing when no period matches the check-in date', function () {
    Carbon::setTestNow('2026-03-12');

    try {
        fakeStripeCheckout();

        $apartment = createPricingApartment([
            'base_price' => 100,
            'extra_guest_price_2' => 20,
            'extra_guest_price_3' => 30,
            'extra_guest_price_4' => 40,
        ]);

        $response = $this->post('/booking-request', bookingPayload($apartment->id, [
            'start_date' => '2026-04-10',
            'end_date' => '2026-04-13',
            'guests_count' => 3,
        ]));

        $response->assertRedirect();

        $reservation = Reservation::query()->latest('id')->firstOrFail();

        expect((float) $reservation->subtotal)->toBe(450.0)
            ->and((float) $reservation->total)->toBe(450.0);
    } finally {
        Carbon::setTestNow();
    }
});

it('uses period pricing when check-in date is inside a period', function () {
    Carbon::setTestNow('2026-03-12');

    try {
        fakeStripeCheckout();

        $apartment = createPricingApartment([
            'base_price' => 100,
            'extra_guest_price_2' => 20,
            'extra_guest_price_3' => 30,
            'extra_guest_price_4' => 40,
        ]);

        Period::query()->create([
            'apartment_id' => $apartment->id,
            'name' => 'Alta stagione',
            'start_date' => '2026-05-01',
            'end_date' => '2026-05-20',
            'base_price' => 220,
            'extra_guest_price_2' => 25,
            'extra_guest_price_3' => 35,
            'extra_guest_price_4' => 45,
        ]);

        $response = $this->post('/booking-request', bookingPayload($apartment->id, [
            'start_date' => '2026-05-10',
            'end_date' => '2026-05-13',
            'guests_count' => 2,
        ]));

        $response->assertRedirect();

        $reservation = Reservation::query()->latest('id')->firstOrFail();

        expect((float) $reservation->subtotal)->toBe(735.0)
            ->and((float) $reservation->total)->toBe(735.0);
    } finally {
        Carbon::setTestNow();
    }
});

it('uses the first period pricing when stay crosses into another period', function () {
    Carbon::setTestNow('2026-03-12');

    try {
        fakeStripeCheckout();

        $apartment = createPricingApartment([
            'base_price' => 100,
            'extra_guest_price_2' => 20,
            'extra_guest_price_3' => 30,
            'extra_guest_price_4' => 40,
        ]);

        Period::query()->create([
            'apartment_id' => $apartment->id,
            'name' => 'Periodo 1',
            'start_date' => '2026-06-10',
            'end_date' => '2026-06-15',
            'base_price' => 180,
            'extra_guest_price_2' => 10,
            'extra_guest_price_3' => 15,
            'extra_guest_price_4' => 20,
        ]);

        Period::query()->create([
            'apartment_id' => $apartment->id,
            'name' => 'Periodo 2',
            'start_date' => '2026-06-15',
            'end_date' => '2026-06-25',
            'base_price' => 300,
            'extra_guest_price_2' => 30,
            'extra_guest_price_3' => 40,
            'extra_guest_price_4' => 50,
        ]);

        $response = $this->post('/booking-request', bookingPayload($apartment->id, [
            'start_date' => '2026-06-14',
            'end_date' => '2026-06-18',
            'guests_count' => 1,
        ]));

        $response->assertRedirect();

        $reservation = Reservation::query()->latest('id')->firstOrFail();

        expect((float) $reservation->subtotal)->toBe(720.0)
            ->and((float) $reservation->total)->toBe(720.0);
    } finally {
        Carbon::setTestNow();
    }
});

it('applies period discount and adds cleaning fee to the final total', function () {
    Carbon::setTestNow('2026-03-12');

    try {
        fakeStripeCheckout();

        $apartment = createPricingApartment([
            'base_price' => 100,
            'extra_guest_price_2' => 20,
            'extra_guest_price_3' => 30,
            'extra_guest_price_4' => 40,
            'cleaning_fee' => 30,
        ]);

        Period::query()->create([
            'apartment_id' => $apartment->id,
            'name' => 'Promo primavera',
            'start_date' => '2026-04-01',
            'end_date' => '2026-04-30',
            'base_price' => 100,
            'extra_guest_price_2' => 20,
            'extra_guest_price_3' => 30,
            'extra_guest_price_4' => 40,
            'discount_percentage' => 10,
        ]);

        $response = $this->post('/booking-request', bookingPayload($apartment->id, [
            'start_date' => '2026-04-10',
            'end_date' => '2026-04-14',
            'guests_count' => 1,
        ]));

        $response->assertRedirect();

        $reservation = Reservation::query()->with('payments')->latest('id')->firstOrFail();
        $pendingPayment = $reservation->payments->firstWhere('status', 'pending');

        expect($pendingPayment)->not->toBeNull()
            ->and((float) $reservation->subtotal)->toBe(400.0)
            ->and((float) $reservation->discount_percent)->toBe(10.0)
            ->and((float) $reservation->total)->toBe(390.0)
            ->and((float) $pendingPayment->amount)->toBe(390.0);
    } finally {
        Carbon::setTestNow();
    }
});

it('uses discounted total including cleaning fee for split payments', function () {
    Carbon::setTestNow('2026-03-12');

    try {
        fakeStripeCheckout();

        $apartment = createPricingApartment([
            'base_price' => 100,
            'cleaning_fee' => 30,
        ]);

        Period::query()->create([
            'apartment_id' => $apartment->id,
            'name' => 'Promo split',
            'start_date' => '2026-04-01',
            'end_date' => '2026-04-30',
            'base_price' => 100,
            'discount_percentage' => 10,
        ]);

        $response = $this->post('/booking-request', bookingPayload($apartment->id, [
            'start_date' => '2026-04-10',
            'end_date' => '2026-04-14',
            'guests_count' => 1,
            'payment_plan' => 'split',
        ]));

        $response->assertRedirect();

        $reservation = Reservation::query()->with('payments')->latest('id')->firstOrFail();
        $deposit = $reservation->payments->firstWhere('step', 'deposit');
        $balance = $reservation->payments->firstWhere('step', 'balance');

        expect($deposit)->not->toBeNull()
            ->and($balance)->not->toBeNull()
            ->and((float) $reservation->total)->toBe(390.0)
            ->and((float) $deposit->amount)->toBe(117.0)
            ->and((float) $balance->amount)->toBe(273.0);
    } finally {
        Carbon::setTestNow();
    }
});

it('stores crib preference on reservation', function () {
    Carbon::setTestNow('2026-03-12');

    try {
        fakeStripeCheckout();

        $apartment = createPricingApartment();

        $response = $this->post('/booking-request', bookingPayload($apartment->id, [
            'needs_crib' => true,
        ]));

        $response->assertRedirect();

        $reservation = Reservation::query()->latest('id')->firstOrFail();

        expect($reservation->needs_crib)->toBeTrue();
    } finally {
        Carbon::setTestNow();
    }
});

/**
 * @param array<string, mixed> $overrides
 */
function createPricingApartment(array $overrides = []): Apartment
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

/**
 * @param array<string, mixed> $overrides
 * @return array<string, mixed>
 */
function bookingPayload(int $apartmentId, array $overrides = []): array
{
    return array_merge([
        'apartment_id' => $apartmentId,
        'email' => 'guest@example.com',
        'phone' => '+39 333 1234567',
        'name' => 'Mario',
        'surname' => 'Rossi',
        'start_date' => '2026-04-10',
        'end_date' => '2026-04-13',
        'guests_count' => 2,
        'notes' => null,
        'payment_plan' => 'full',
        'payment_locale' => 'it',
    ], $overrides);
}

function fakeStripeCheckout(): void
{
    config(['services.stripe.secret' => 'sk_test_fake']);

    Http::fake([
        'https://api.stripe.com/v1/checkout/sessions' => Http::response([
            'id' => 'cs_test_123',
            'url' => 'https://checkout.stripe.test/session/cs_test_123',
        ], 200),
    ]);
}
