<?php

use App\Models\Apartment;
use App\Models\User;
use App\Models\UserGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

it('redirects guest users to home with error when stripe session creation fails', function () {
    Carbon::setTestNow('2026-03-12');

    try {
        config(['services.stripe.secret' => null]);

        $apartment = createApartmentForStripeFallback();

        $response = $this->post('/booking-request', bookingPayloadForStripeFallback($apartment->id));

        $response->assertRedirect(route('home', ['payment' => 'failed']));
        $response->assertSessionHasErrors('payment_plan');
    } finally {
        Carbon::setTestNow();
    }
});

it('redirects authenticated users to private area with error when stripe session creation fails', function () {
    Carbon::setTestNow('2026-03-12');

    try {
        config(['services.stripe.secret' => null]);

        $customerGroup = UserGroup::query()->firstOrCreate(
            ['slug' => UserGroup::CUSTOMER_SLUG],
            ['name' => 'Customer'],
        );

        $user = User::factory()->create([
            'user_group_id' => $customerGroup->id,
        ]);

        $apartment = createApartmentForStripeFallback();

        $response = $this
            ->actingAs($user)
            ->post('/booking-request', bookingPayloadForStripeFallback($apartment->id, [
                'email' => 'ignored@example.com',
            ]));

        $response->assertRedirect(route('private-area.index', ['payment' => 'failed']));
        $response->assertSessionHasErrors('payment');
    } finally {
        Carbon::setTestNow();
    }
});

function createApartmentForStripeFallback(): Apartment
{
    return Apartment::query()->create([
        'name_it' => 'Test Apartment',
        'name_en' => 'Test Apartment',
        'address_it' => 'Via Test 1',
        'address_en' => 'Test Street 1',
        'rooms_count' => 2,
        'beds_count' => 2,
        'bathrooms_count' => 1,
        'max_guests' => 4,
        'base_price' => 100,
        'extra_guest_price_2' => 20,
        'extra_guest_price_3' => 30,
        'extra_guest_price_4' => 40,
    ]);
}

/**
 * @param array<string, mixed> $overrides
 * @return array<string, mixed>
 */
function bookingPayloadForStripeFallback(int $apartmentId, array $overrides = []): array
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
