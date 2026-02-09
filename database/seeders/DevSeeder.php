<?php

namespace Database\Seeders;

use App\Models\Apartment;
use App\Models\BlockedDate;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\User;
use App\Models\UserGroup;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;

class DevSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminGroup = UserGroup::firstOrCreate(
            ['slug' => UserGroup::ADMIN_SLUG],
            ['name' => 'Admin'],
        );

        $customerGroup = UserGroup::firstOrCreate(
            ['slug' => UserGroup::CUSTOMER_SLUG],
            ['name' => 'Customer'],
        );

        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test',
                'surname' => 'User',
                'password' => Hash::make('password'),
                'user_group_id' => $adminGroup->id,
            ],
        );

        User::firstOrCreate(
            ['email' => 'cliente@example.com'],
            [
                'name' => 'Test',
                'surname' => 'Cliente',
                'password' => Hash::make('password'),
                'user_group_id' => $customerGroup->id,
            ],
        );

        $apartment = Apartment::query()->first();

        if (! $apartment) {
            return;
        }

        $customerOne = User::firstOrCreate(
            ['email' => 'maria.rossi@example.com'],
            [
                'name' => 'Maria',
                'surname' => 'Rossi',
                'password' => Hash::make('password'),
                'user_group_id' => $customerGroup->id,
            ],
        );

        $customerTwo = User::firstOrCreate(
            ['email' => 'luca.bianchi@example.com'],
            [
                'name' => 'Luca',
                'surname' => 'Bianchi',
                'password' => Hash::make('password'),
                'user_group_id' => $customerGroup->id,
            ],
        );

        $today = Carbon::today();

        $reservationOne = Reservation::create([
            'customer_id' => $customerOne->id,
            'apartment_id' => $apartment->id,
            'status' => Reservation::STATUS_CONFIRMED,
            'guests_count' => 2,
            'start_date' => $today->copy()->addDays(10),
            'end_date' => $today->copy()->addDays(13),
            'is_paid' => false,
            'subtotal' => 300,
            'discount_percent' => 0,
            'total' => 300,
            'total_paid' => 90,
            'notes' => 'Pagamento caparra.',
        ]);

        Payment::create([
            'reservation_id' => $reservationOne->id,
            'provider' => 'stripe',
            'step' => Payment::STEP_DEPOSIT,
            'status' => Payment::STATUS_PAID,
            'amount' => 90,
            'currency' => 'eur',
            'locale' => 'it',
            'paid_at' => Carbon::now()->subDays(1),
        ]);

        Payment::create([
            'reservation_id' => $reservationOne->id,
            'provider' => 'stripe',
            'step' => Payment::STEP_BALANCE,
            'status' => Payment::STATUS_PENDING,
            'amount' => 210,
            'currency' => 'eur',
            'due_at' => $reservationOne->start_date?->copy()->subDays(7),
            'locale' => 'it',
        ]);

        $reservationTwo = Reservation::create([
            'customer_id' => $customerTwo->id,
            'apartment_id' => $apartment->id,
            'status' => Reservation::STATUS_PENDING,
            'guests_count' => 1,
            'start_date' => $today->copy()->addDays(20),
            'end_date' => $today->copy()->addDays(22),
            'is_paid' => false,
            'subtotal' => 160,
            'discount_percent' => 0,
            'total' => 160,
            'total_paid' => 0,
            'notes' => 'Richiesta in attesa.',
        ]);

        Payment::create([
            'reservation_id' => $reservationTwo->id,
            'provider' => 'stripe',
            'step' => Payment::STEP_FULL,
            'status' => Payment::STATUS_PENDING,
            'amount' => 160,
            'currency' => 'eur',
            'locale' => 'en',
        ]);

        $reservationThree = Reservation::create([
            'customer_id' => $customerTwo->id,
            'apartment_id' => $apartment->id,
            'status' => Reservation::STATUS_CANCELLED,
            'cancelled_by' => Reservation::CANCELLED_BY_CUSTOMER,
            'guests_count' => 2,
            'start_date' => $today->copy()->subDays(20),
            'end_date' => $today->copy()->subDays(17),
            'is_paid' => false,
            'subtotal' => 240,
            'discount_percent' => 0,
            'total' => 240,
            'total_paid' => 0,
            'notes' => 'Prenotazione cancellata.',
        ]);

        Payment::create([
            'reservation_id' => $reservationThree->id,
            'provider' => 'stripe',
            'step' => Payment::STEP_FULL,
            'status' => Payment::STATUS_REFUNDED,
            'amount' => 240,
            'currency' => 'eur',
            'locale' => 'it',
            'paid_at' => Carbon::now()->subDays(30),
            'refunded_at' => Carbon::now()->subDays(25),
            'refund_amount' => 72,
        ]);

        BlockedDate::create([
            'apartment_id' => $apartment->id,
            'start_date' => $today->copy()->addDays(5),
            'end_date' => $today->copy()->addDays(7),
            'reason' => 'Manutenzione',
        ]);

        BlockedDate::create([
            'apartment_id' => $apartment->id,
            'start_date' => $today->copy()->addDays(30),
            'end_date' => $today->copy()->addDays(32),
            'reason' => 'Uso privato',
        ]);
    }
}
