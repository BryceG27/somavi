<?php

namespace App\Services;

use App\Models\Payment;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class StripeCheckoutService
{
    private const STRIPE_ENDPOINT = 'https://api.stripe.com/v1/checkout/sessions';

    public function createSession(Payment $payment): array
    {
        $secret = config('services.stripe.secret');

        if (! $secret) {
            throw new RuntimeException('Stripe secret key is not configured.');
        }

        $amount = (float) $payment->amount;

        if ($amount <= 0) {
            throw new RuntimeException('Invalid payment amount.');
        }

        $currency = config('services.stripe.currency', 'eur');
        $locale = $this->resolveLocale($payment->locale);
        $reservation = $payment->reservation;

        $payload = [
            'mode' => 'payment',
            'success_url' => config('services.stripe.success_url') ?? url('/area-privata'),
            'cancel_url' => config('services.stripe.cancel_url') ?? url('/'),
            'customer_email' => $reservation?->customer?->email,
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => $currency,
                        'unit_amount' => (int) round($amount * 100),
                        'product_data' => [
                            'name' => $this->resolveProductName($payment, $locale),
                        ],
                    ],
                    'quantity' => 1,
                ],
            ],
            'metadata' => [
                'reservation_id' => $reservation?->id,
                'payment_id' => $payment->id,
                'payment_step' => $payment->step,
                'locale' => $payment->locale,
            ],
            'payment_intent_data' => [
                'metadata' => [
                    'reservation_id' => $reservation?->id,
                    'payment_id' => $payment->id,
                    'payment_step' => $payment->step,
                    'locale' => $payment->locale,
                ],
            ],
        ];

        if ($locale !== 'auto') {
            $payload['locale'] = $locale;
        }

        $response = Http::withBasicAuth($secret, '')
            ->asForm()
            ->post(self::STRIPE_ENDPOINT, $this->flatten($payload));

        if (! $response->successful()) {
            throw new RuntimeException('Stripe API error: '.$response->body());
        }

        $data = $response->json();

        return [
            'id' => Arr::get($data, 'id'),
            'url' => Arr::get($data, 'url'),
        ];
    }

    private function resolveLocale(?string $locale): string
    {
        $locale = strtolower((string) $locale);

        return in_array($locale, ['it', 'en'], true) ? $locale : 'auto';
    }

    private function resolveProductName(Payment $payment, string $locale): string
    {
        $reference = $locale === 'en' ? 'Reservation' : 'Prenotazione';
        $suffix = match ($payment->step) {
            'deposit' => $locale === 'en' ? 'Deposit' : 'Caparra',
            'balance' => $locale === 'en' ? 'Balance' : 'Saldo',
            default => $locale === 'en' ? 'Total' : 'Totale',
        };

        return sprintf('%s #%d - %s', $reference, $payment->reservation_id, $suffix);
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
