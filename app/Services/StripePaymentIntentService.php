<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class StripePaymentIntentService
{
    private const STRIPE_ENDPOINT = 'https://api.stripe.com/v1/payment_intents';

    public function capture(string $paymentIntentId): array
    {
        return $this->post("{$paymentIntentId}/capture");
    }

    public function cancel(string $paymentIntentId): array
    {
        return $this->post("{$paymentIntentId}/cancel");
    }

    /**
     * @return array<string, mixed>
     */
    private function post(string $path): array
    {
        $secret = config('services.stripe.secret');

        if (! $secret) {
            throw new RuntimeException('Stripe secret key is not configured.');
        }

        $response = Http::withBasicAuth($secret, '')
            ->asForm()
            ->post(self::STRIPE_ENDPOINT.'/'.$path);

        if (! $response->successful()) {
            throw new RuntimeException('Stripe API error: '.$response->body());
        }

        return $response->json();
    }
}
