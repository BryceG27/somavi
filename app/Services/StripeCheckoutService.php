<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Reservation;
use App\Support\LocalePreference;
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
        $reservation = $payment->reservation?->loadMissing(['customer', 'apartment', 'payments']);
        $defaultSuccessUrl = route('payments.stripe.success').'?session_id={CHECKOUT_SESSION_ID}';
        $defaultCancelUrl = route('payments.stripe.cancel', ['payment_id' => $payment->id]);
        $metadata = $this->buildMetadata($payment, $reservation, $locale);
        $productDescription = $this->resolveProductDescription($payment, $reservation, $locale);
        $paymentIntentDescription = $this->resolvePaymentIntentDescription($payment, $reservation, $locale);

        $payload = [
            'mode' => 'payment',
            'success_url' => config('services.stripe.success_url') ?: $defaultSuccessUrl,
            'cancel_url' => config('services.stripe.cancel_url') ?: $defaultCancelUrl,
            'customer_email' => $reservation?->customer?->email,
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => $currency,
                        'unit_amount' => (int) round($amount * 100),
                        'product_data' => [
                            'name' => $this->resolveProductName($payment, $locale),
                            'description' => $productDescription,
                        ],
                    ],
                    'quantity' => 1,
                ],
            ],
            'metadata' => $metadata,
            'payment_intent_data' => [
                'description' => $paymentIntentDescription,
                'metadata' => $metadata,
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
        return LocalePreference::normalize($locale);
    }

    private function resolveProductName(Payment $payment, string $locale): string
    {
        $reservation = $payment->reservation;
        $apartmentName = $this->resolveApartmentName($reservation, $locale);
        $suffix = match ($payment->step) {
            'deposit' => $locale === 'en' ? 'Deposit' : 'Caparra',
            'balance' => $locale === 'en' ? 'Balance' : 'Saldo',
            default => $locale === 'en' ? 'Total' : 'Totale',
        };

        if ($apartmentName !== '') {
            return sprintf('%s - %s', $apartmentName, $suffix);
        }

        $reference = $locale === 'en' ? 'Reservation' : 'Prenotazione';

        return sprintf('%s #%d - %s', $reference, $payment->reservation_id, $suffix);
    }

    private function resolveProductDescription(Payment $payment, ?Reservation $reservation, string $locale): string
    {
        $apartmentName = $this->resolveApartmentName($reservation, $locale);
        $startDate = $reservation?->start_date?->toDateString();
        $endDate = $reservation?->end_date?->toDateString();
        $paymentPlan = $this->resolvePaymentPlan($reservation);
        $stepLabel = match ($payment->step) {
            Payment::STEP_DEPOSIT => $locale === 'en' ? 'Deposit' : 'Caparra',
            Payment::STEP_BALANCE => $locale === 'en' ? 'Balance' : 'Saldo',
            default => $locale === 'en' ? 'Full amount' : 'Importo totale',
        };
        $planLabel = $paymentPlan === 'split'
            ? ($locale === 'en' ? 'Split payment' : 'Pagamento a rate')
            : ($locale === 'en' ? 'Full payment' : 'Pagamento unico');

        $parts = [];

        if ($apartmentName !== '') {
            $parts[] = $apartmentName;
        }

        if ($startDate && $endDate) {
            $parts[] = "{$startDate} - {$endDate}";
        }

        $parts[] = "{$stepLabel} ({$planLabel})";

        return implode(' | ', $parts);
    }

    private function resolvePaymentIntentDescription(Payment $payment, ?Reservation $reservation, string $locale): string
    {
        $apartmentName = $this->resolveApartmentName($reservation, $locale);
        $startDate = $reservation?->start_date?->toDateString();
        $endDate = $reservation?->end_date?->toDateString();
        $step = $payment->step;
        $base = $locale === 'en' ? 'Reservation payment' : 'Pagamento prenotazione';

        $parts = [$base.' #'.$payment->reservation_id];

        if ($apartmentName !== '') {
            $parts[] = $apartmentName;
        }

        if ($startDate && $endDate) {
            $parts[] = "{$startDate} - {$endDate}";
        }

        $parts[] = $step;

        return implode(' | ', $parts);
    }

    /**
     * @return array<string, string>
     */
    private function buildMetadata(Payment $payment, ?Reservation $reservation, string $locale): array
    {
        $apartment = $reservation?->apartment;
        $apartmentName = $this->resolveApartmentName($reservation, $locale);
        $paymentPlan = $this->resolvePaymentPlan($reservation);

        $metadata = [
            'reservation_id' => (string) ($reservation?->id ?? ''),
            'payment_id' => (string) $payment->id,
            'payment_step' => (string) $payment->step,
            'payment_plan' => $paymentPlan,
            'locale' => (string) $payment->locale,
            'apartment_id' => (string) ($apartment?->id ?? ''),
            'apartment_name' => $apartmentName,
            'stay_start' => (string) ($reservation?->start_date?->toDateString() ?? ''),
            'stay_end' => (string) ($reservation?->end_date?->toDateString() ?? ''),
            'guests_count' => (string) ($reservation?->guests_count ?? ''),
        ];

        return array_filter($metadata, static fn ($value) => $value !== '');
    }

    private function resolveApartmentName(?Reservation $reservation, string $locale): string
    {
        $apartment = $reservation?->apartment;

        if (! $apartment) {
            return '';
        }

        if ($locale === 'en' && (string) $apartment->name_en !== '') {
            return (string) $apartment->name_en;
        }

        return (string) ($apartment->name_it ?? $apartment->name_en ?? '');
    }

    private function resolvePaymentPlan(?Reservation $reservation): string
    {
        if (! $reservation) {
            return 'full';
        }

        $hasSplitSteps = $reservation->payments
            ->contains(fn (Payment $item) => in_array($item->step, [Payment::STEP_DEPOSIT, Payment::STEP_BALANCE], true));

        return $hasSplitSteps ? 'split' : 'full';
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
