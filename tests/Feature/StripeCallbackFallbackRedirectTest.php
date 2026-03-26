<?php

use App\Models\User;
use App\Models\UserGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('redirects guest users to home with error on failed stripe success callback fallback', function () {
    $response = $this->get('/payments/stripe/success');

    $response->assertRedirect(route('home', ['payment' => 'failed']));
    $response->assertSessionHasErrors('payment_plan');
});

it('redirects authenticated users to private area on failed stripe success callback fallback', function () {
    $customerGroup = UserGroup::query()->firstOrCreate(
        ['slug' => UserGroup::CUSTOMER_SLUG],
        ['name' => 'Customer'],
    );

    $user = User::factory()->create([
        'user_group_id' => $customerGroup->id,
    ]);

    $response = $this
        ->actingAs($user)
        ->get('/payments/stripe/success');

    $response->assertRedirect(route('private-area.index', ['payment' => 'failed']));
});

it('redirects guest users to home with error on cancelled stripe callback', function () {
    $response = $this->get('/payments/stripe/cancel');

    $response->assertRedirect(route('home', ['payment' => 'cancelled']));
    $response->assertSessionHasErrors('payment_plan');
});
