<?php

use App\Jobs\CheckExternalAvailability;
use App\Mail\ReservationConfirmedMail;
use App\Models\Apartment;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\User;
use App\Models\UserGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

it('confirms pending reservation and sends confirmation email', function () {
    config()->set('services.availability.mode', 'assume_available');
    Mail::fake();

    [$customer, $apartment] = createCustomerAndApartment('en');

    $reservation = Reservation::query()->create([
        'customer_id' => $customer->id,
        'apartment_id' => $apartment->id,
        'status' => Reservation::STATUS_PENDING,
        'guests_count' => 2,
        'start_date' => '2026-07-10',
        'end_date' => '2026-07-14',
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
        'locale' => 'it',
        'paid_at' => now(),
    ]);

    (new CheckExternalAvailability($reservation->id))->handle();

    expect($reservation->fresh()->status)->toBe(Reservation::STATUS_CONFIRMED);

    Mail::assertSent(ReservationConfirmedMail::class, function (ReservationConfirmedMail $mail) use ($customer): bool {
        return $mail->hasTo($customer->email) && $mail->preferredLocale === 'en';
    });
});

it('periodic command verifies pending paid reservations', function () {
    config()->set('services.availability.mode', 'assume_available');
    Mail::fake();

    [$customer, $apartment] = createCustomerAndApartment('it');

    $reservation = Reservation::query()->create([
        'customer_id' => $customer->id,
        'apartment_id' => $apartment->id,
        'status' => Reservation::STATUS_PENDING,
        'guests_count' => 2,
        'start_date' => '2026-08-10',
        'end_date' => '2026-08-14',
        'subtotal' => 120,
        'discount_percent' => 0,
        'total' => 120,
        'total_paid' => 120,
        'is_paid' => true,
    ]);

    Payment::query()->create([
        'reservation_id' => $reservation->id,
        'provider' => 'stripe',
        'step' => Payment::STEP_FULL,
        'status' => Payment::STATUS_PAID,
        'amount' => 120,
        'currency' => 'eur',
        'locale' => 'it',
        'paid_at' => now(),
    ]);

    Artisan::call('reservations:verify-pending-availability', ['--limit' => 50]);

    expect($reservation->fresh()->status)->toBe(Reservation::STATUS_CONFIRMED);

    Mail::assertSent(ReservationConfirmedMail::class, 1);
});

function createCustomerAndApartment(string $locale = 'it'): array
{
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
        'preferred_locale' => $locale,
    ]);

    $apartment = Apartment::query()->create([
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
    ]);

    return [$customer, $apartment];
}
