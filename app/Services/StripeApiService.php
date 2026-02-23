<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class StripeApiService
{
    private const STRIPE_ENDPOINT = 'https://api.stripe.com/v1';

    /**
     * @return array<string, mixed>
     */
    public function getCheckoutSession(string $sessionId, bool $expandPaymentIntent = true): array
    {
        $query = [];

        if ($expandPaymentIntent) {
            $query['expand'] = ['payment_intent'];
        }

        return $this->get('/checkout/sessions/'.$sessionId, $query);
    }

    /**
     * @return array<string, mixed>
     */
    public function getPaymentIntent(string $paymentIntentId): array
    {
        return $this->get('/payment_intents/'.$paymentIntentId);
    }

    /**
     * @param array<string, mixed> $query
     * @return array<string, mixed>
     */
    private function get(string $path, array $query = []): array
    {
        $secret = config('services.stripe.secret');

        if (! $secret) {
            throw new RuntimeException('Stripe secret key is not configured.');
        }

        $response = Http::withBasicAuth($secret, '')
            ->get(self::STRIPE_ENDPOINT.$path, $query);

        if (! $response->successful()) {
            throw new RuntimeException('Stripe API error: '.$response->body());
        }

        $data = $response->json();

        if (! is_array($data)) {
            throw new RuntimeException('Invalid Stripe API response.');
        }

        return $data;
    }
}
