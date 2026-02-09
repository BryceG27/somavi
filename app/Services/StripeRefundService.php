<?php

namespace App\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class StripeRefundService
{
    private const STRIPE_ENDPOINT = 'https://api.stripe.com/v1/refunds';

    public function refund(string $paymentIntentId, float $amount): string
    {
        $secret = config('services.stripe.secret');

        if (! $secret) {
            throw new RuntimeException('Stripe secret key is not configured.');
        }

        $payload = [
            'payment_intent' => $paymentIntentId,
            'amount' => (int) round($amount * 100),
        ];

        $response = Http::withBasicAuth($secret, '')
            ->asForm()
            ->post(self::STRIPE_ENDPOINT, $this->flatten($payload));

        if (! $response->successful()) {
            throw new RuntimeException('Stripe refund error: '.$response->body());
        }

        $data = $response->json();

        return (string) Arr::get($data, 'id');
    }

    private function flatten(array $params, string $prefix = ''): array
    {
        $result = [];

        foreach ($params as $key => $value) {
            $fullKey = $prefix === '' ? (string) $key : sprintf('%s[%s]', $prefix, $key);

            if (is_array($value)) {
                $result += $this->flatten($value, $fullKey);
                continue;
            }

            $result[$fullKey] = $value;
        }

        return $result;
    }
}
