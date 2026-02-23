<?php

use App\Mail\PaymentReminderMail;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\User;
use App\Models\UserGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

it('prefers customer locale for reminder emails', function () {
    $today = Carbon::parse('2026-02-23');
    Carbon::setTestNow($today);

    try {
        Mail::fake();

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
            'preferred_locale' => 'en',
        ]);

        $apartment = createReminderApartment();

        $reservation = Reservation::query()->create([
            'customer_id' => $customer->id,
            'apartment_id' => $apartment->id,
            'status' => Reservation::STATUS_CONFIRMED,
            'guests_count' => 2,
            'start_date' => $today->copy()->addDays(14)->toDateString(),
            'end_date' => $today->copy()->addDays(17)->toDateString(),
            'subtotal' => 300,
            'discount_percent' => 0,
            'total' => 300,
            'total_paid' => 90,
            'is_paid' => false,
        ]);

        Payment::query()->create([
            'reservation_id' => $reservation->id,
            'provider' => 'stripe',
            'step' => Payment::STEP_BALANCE,
            'status' => Payment::STATUS_PENDING,
            'amount' => 210,
            'currency' => 'eur',
            'locale' => 'it',
        ]);

        Artisan::call('reservations:send-payment-reminders');

        Mail::assertSent(PaymentReminderMail::class, function (PaymentReminderMail $mail) use ($customer): bool {
            return $mail->hasTo($customer->email) && $mail->preferredLocale === 'en';
        });

        expect($reservation->fresh()->reminder_14_sent_at)->not->toBeNull();
    } finally {
        Carbon::setTestNow();
    }
});

function createReminderApartment(): \App\Models\Apartment
{
    return \App\Models\Apartment::query()->create([
        'name_it' => 'Reminder Apartment',
        'name_en' => 'Reminder Apartment',
        'address_it' => 'Via Reminder 1',
        'address_en' => 'Reminder Street 1',
        'rooms_count' => 2,
        'beds_count' => 2,
        'bathrooms_count' => 1,
        'max_guests' => 4,
        'base_price' => 50,
        'extra_guest_price_2' => 10,
        'extra_guest_price_3' => 10,
        'extra_guest_price_4' => 10,
    ]);
}
