<?php

namespace App\Http\Controllers;

use App\Jobs\CheckExternalAvailability;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\UserGroup;
use App\Services\StripeApiService;
use App\Services\StripeCheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class StripeController extends Controller
{
    public function checkout(Request $request, Reservation $reservation, StripeCheckoutService $service): JsonResponse
    {
        $user = $request->user();

        if ($user) {
            if ($user->userGroup?->slug !== UserGroup::CUSTOMER_SLUG || $user->id !== $reservation->customer_id) {
                abort(403, 'Accesso non autorizzato');
            }
        } else {
            $email = strtolower(trim((string) $request->input('email')));

            if ($email === '' || $email !== strtolower((string) $reservation->customer?->email)) {
                abort(403, 'Accesso non autorizzato');
            }
        }

        $payment = $reservation->payments()
            ->where('status', Payment::STATUS_PENDING)
            ->orderByRaw("case when step = 'deposit' then 0 when step = 'balance' then 1 else 2 end")
            ->first();

        if (! $payment) {
            return response()->json(['message' => 'Pagamento non disponibile.'], 422);
        }

        $session = $service->createSession($payment->load('reservation.customer'));

        $payment->forceFill([
            'stripe_checkout_session_id' => $session['id'],
        ])->save();

        return response()->json([
            'url' => $session['url'],
        ]);
    }

    public function success(Request $request, StripeApiService $stripeApi): RedirectResponse
    {
        $sessionId = trim((string) $request->query('session_id'));

        if ($sessionId === '') {
            return $this->redirectWithPaymentState('failed');
        }

        try {
            $session = $stripeApi->getCheckoutSession($sessionId, true);
            $payment = $this->resolvePaymentFromStripePayload($session);

            if (! $payment || ! $this->isStripePayloadConsistent($payment, $session)) {
                return $this->redirectWithPaymentState('failed');
            }

            $this->syncPaymentFromCheckoutSession($payment, $session);

            return $this->redirectWithPaymentState(
                $this->resolveRedirectStateFromPayment($payment->fresh())
            );
        } catch (Throwable $exception) {
            Log::warning('Stripe success callback failed.', [
                'session_id' => $sessionId,
                'message' => $exception->getMessage(),
            ]);

            return $this->redirectWithPaymentState('failed');
        }
    }

    public function cancel(): RedirectResponse
    {
        return $this->redirectWithPaymentState('cancelled');
    }

    public function webhook(Request $request, StripeApiService $stripeApi): Response
    {
        $secret = config('services.stripe.webhook_secret');
        $payload = (string) $request->getContent();
        $signature = (string) $request->header('Stripe-Signature');

        if ($secret && ! $this->verifySignature($payload, $signature, $secret)) {
            return response('Invalid signature.', 400);
        }

        $event = json_decode($payload, true);

        if (! is_array($event)) {
            return response('Invalid payload.', 400);
        }

        try {
            $this->handleWebhookEvent($event, $stripeApi);
        } catch (Throwable $exception) {
            Log::warning('Stripe webhook processing failed.', [
                'event_id' => $event['id'] ?? null,
                'event_type' => $event['type'] ?? null,
                'message' => $exception->getMessage(),
            ]);

            return response('Webhook processing failed.', 500);
        }

        return response('ok', 200);
    }

    /**
     * @param array<string, mixed> $event
     */
    private function handleWebhookEvent(array $event, StripeApiService $stripeApi): void
    {
        $eventType = (string) ($event['type'] ?? '');
        $eventObject = $event['data']['object'] ?? [];

        if (! is_array($eventObject)) {
            return;
        }

        if (in_array($eventType, [
            'checkout.session.completed',
            'checkout.session.async_payment_succeeded',
            'checkout.session.async_payment_failed',
            'checkout.session.expired',
        ], true)) {
            $sessionId = (string) ($eventObject['id'] ?? '');

            if ($sessionId === '') {
                return;
            }

            $session = $stripeApi->getCheckoutSession($sessionId, true);
            $payment = $this->resolvePaymentFromStripePayload($session);

            if (! $payment) {
                return;
            }

            if (! $this->isStripePayloadConsistent($payment, $session)) {
                Log::warning('Stripe checkout session failed consistency checks.', [
                    'session_id' => $sessionId,
                    'payment_id' => $payment->id,
                ]);
                return;
            }

            $this->syncPaymentFromCheckoutSession($payment, $session);
            return;
        }

        if (in_array($eventType, [
            'payment_intent.succeeded',
            'payment_intent.canceled',
            'payment_intent.payment_failed',
        ], true)) {
            $intentId = (string) ($eventObject['id'] ?? '');

            if ($intentId === '') {
                return;
            }

            $intent = $stripeApi->getPaymentIntent($intentId);
            $payment = $this->resolvePaymentFromStripePayload($intent);

            if (! $payment) {
                return;
            }

            if (! $this->isStripePayloadConsistent($payment, $intent)) {
                Log::warning('Stripe payment intent failed consistency checks.', [
                    'payment_intent_id' => $intentId,
                    'payment_id' => $payment->id,
                ]);
                return;
            }

            $this->syncPaymentFromPaymentIntent($payment, $intent);
        }
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function resolvePaymentFromStripePayload(array $payload): ?Payment
    {
        $metadata = $this->extractMetadata($payload);
        $paymentId = (int) ($metadata['payment_id'] ?? 0);

        if ($paymentId <= 0) {
            return null;
        }

        return Payment::query()
            ->with('reservation')
            ->find($paymentId);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function isStripePayloadConsistent(Payment $payment, array $payload): bool
    {
        $metadata = $this->extractMetadata($payload);

        $paymentId = (int) ($metadata['payment_id'] ?? 0);
        if ($paymentId > 0 && $paymentId !== (int) $payment->id) {
            return false;
        }

        $reservationId = (int) ($metadata['reservation_id'] ?? 0);
        if ($reservationId > 0 && $reservationId !== (int) $payment->reservation_id) {
            return false;
        }

        $payloadCurrency = strtolower((string) ($payload['currency'] ?? ''));
        if ($payloadCurrency !== '' && $payloadCurrency !== strtolower((string) $payment->currency)) {
            return false;
        }

        $payloadAmount = $this->extractAmountInCents($payload);
        $expectedAmount = (int) round(((float) $payment->amount) * 100);
        if ($payloadAmount !== null && $payloadAmount !== $expectedAmount) {
            return false;
        }

        $sessionId = $this->extractCheckoutSessionId($payload);
        if (
            $sessionId !== null
            && $payment->stripe_checkout_session_id
            && $sessionId !== $payment->stripe_checkout_session_id
        ) {
            return false;
        }

        $paymentIntentId = $this->extractPaymentIntentId($payload);
        if (
            $paymentIntentId !== null
            && $payment->stripe_payment_intent_id
            && $paymentIntentId !== $payment->stripe_payment_intent_id
        ) {
            return false;
        }

        return true;
    }

    /**
     * @param array<string, mixed> $session
     */
    private function syncPaymentFromCheckoutSession(Payment $payment, array $session): void
    {
        $sessionId = $this->extractCheckoutSessionId($session);
        $paymentIntentId = $this->extractPaymentIntentId($session);

        $attributes = [];

        if ($sessionId !== null) {
            $attributes['stripe_checkout_session_id'] = $sessionId;
        }

        if ($paymentIntentId !== null) {
            $attributes['stripe_payment_intent_id'] = $paymentIntentId;
        }

        if ($attributes !== []) {
            $payment->forceFill($attributes)->save();
        }

        $intentStatus = $this->extractPaymentIntentStatus($session);
        if ($intentStatus !== null) {
            $this->syncPaymentStatusFromStripe($payment, $intentStatus);
            return;
        }

        $sessionStatus = (string) ($session['payment_status'] ?? '');

        if ($sessionStatus === 'paid') {
            $this->syncPaymentStatusFromStripe($payment, 'succeeded');
        }
    }

    /**
     * @param array<string, mixed> $intent
     */
    private function syncPaymentFromPaymentIntent(Payment $payment, array $intent): void
    {
        $intentId = $this->extractPaymentIntentId($intent);

        if ($intentId !== null) {
            $payment->forceFill([
                'stripe_payment_intent_id' => $intentId,
            ])->save();
        }

        $intentStatus = (string) ($intent['status'] ?? '');
        if ($intentStatus === '') {
            return;
        }

        $this->syncPaymentStatusFromStripe($payment, $intentStatus);
    }

    private function syncPaymentStatusFromStripe(Payment $payment, string $stripeStatus): void
    {
        if ($stripeStatus === 'requires_capture') {
            if ($payment->status === Payment::STATUS_PENDING) {
                $payment->forceFill([
                    'status' => Payment::STATUS_AUTHORIZED,
                ])->save();
            }
            return;
        }

        if ($stripeStatus === 'succeeded') {
            $wasJustMarkedPaid = false;

            if ($payment->status !== Payment::STATUS_PAID && $payment->status !== Payment::STATUS_REFUNDED) {
                $payment->forceFill([
                    'status' => Payment::STATUS_PAID,
                    'paid_at' => $payment->paid_at ?? Carbon::now(),
                ])->save();
                $wasJustMarkedPaid = true;
            }

            $this->syncReservationTotals($payment);
            $this->startExternalAvailabilityCheckWorkflow($payment, $wasJustMarkedPaid);
            return;
        }

        if ($stripeStatus === 'canceled') {
            if ($payment->status !== Payment::STATUS_PAID && $payment->status !== Payment::STATUS_REFUNDED) {
                $payment->forceFill([
                    'status' => Payment::STATUS_VOIDED,
                ])->save();
            }
        }
    }

    private function syncReservationTotals(Payment $payment): void
    {
        $reservation = $payment->reservation;

        if (! $reservation) {
            return;
        }

        $totalPaid = (float) $reservation->payments()
            ->where('status', Payment::STATUS_PAID)
            ->sum('amount');

        $reservation->forceFill([
            'total_paid' => $totalPaid,
            'is_paid' => $totalPaid >= (float) $reservation->total,
        ])->save();
    }

    private function startExternalAvailabilityCheckWorkflow(Payment $payment, bool $wasJustMarkedPaid): void
    {
        if (! $wasJustMarkedPaid) {
            return;
        }

        $reservationId = (int) $payment->reservation_id;

        if ($reservationId <= 0) {
            return;
        }

        $reservation = $payment->reservation;
        if (! $reservation) {
            return;
        }

        $paidPaymentsCount = (int) $reservation->payments()
            ->where('status', Payment::STATUS_PAID)
            ->count();

        if ($paidPaymentsCount !== 1) {
            return;
        }

        if ($reservation->status === Reservation::STATUS_AWAITING_PAYMENT) {
            $updated = Reservation::query()
                ->whereKey($reservationId)
                ->where('status', Reservation::STATUS_AWAITING_PAYMENT)
                ->update([
                    'status' => Reservation::STATUS_PENDING,
                    'updated_at' => Carbon::now(),
                ]);

            if ($updated !== 1) {
                return;
            }
        } elseif ($reservation->status !== Reservation::STATUS_PENDING) {
            return;
        }

        CheckExternalAvailability::dispatch($reservationId);
    }

    private function resolveRedirectStateFromPayment(?Payment $payment): string
    {
        if (! $payment) {
            return 'failed';
        }

        return match ($payment->status) {
            Payment::STATUS_PAID => 'paid',
            Payment::STATUS_AUTHORIZED => 'authorized',
            default => 'failed',
        };
    }

    private function redirectWithPaymentState(string $state): RedirectResponse
    {
        return redirect()->route('private-area.index', ['payment' => $state]);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function extractMetadata(array $payload): array
    {
        if (isset($payload['metadata']) && is_array($payload['metadata'])) {
            return $payload['metadata'];
        }

        if (isset($payload['payment_intent']) && is_array($payload['payment_intent'])) {
            $intent = $payload['payment_intent'];

            if (isset($intent['metadata']) && is_array($intent['metadata'])) {
                return $intent['metadata'];
            }
        }

        return [];
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function extractCheckoutSessionId(array $payload): ?string
    {
        if (($payload['object'] ?? null) === 'checkout.session') {
            $sessionId = (string) ($payload['id'] ?? '');
            return $sessionId !== '' ? $sessionId : null;
        }

        return null;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function extractPaymentIntentId(array $payload): ?string
    {
        if (($payload['object'] ?? null) === 'payment_intent') {
            $intentId = (string) ($payload['id'] ?? '');
            return $intentId !== '' ? $intentId : null;
        }

        if (isset($payload['payment_intent']) && is_string($payload['payment_intent']) && $payload['payment_intent'] !== '') {
            return $payload['payment_intent'];
        }

        if (isset($payload['payment_intent']) && is_array($payload['payment_intent'])) {
            $intentId = (string) ($payload['payment_intent']['id'] ?? '');
            return $intentId !== '' ? $intentId : null;
        }

        return null;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function extractPaymentIntentStatus(array $payload): ?string
    {
        if (isset($payload['payment_intent']) && is_array($payload['payment_intent'])) {
            $status = (string) ($payload['payment_intent']['status'] ?? '');
            return $status !== '' ? $status : null;
        }

        return null;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function extractAmountInCents(array $payload): ?int
    {
        if (isset($payload['amount_total']) && is_numeric($payload['amount_total'])) {
            return (int) $payload['amount_total'];
        }

        if (isset($payload['amount']) && is_numeric($payload['amount'])) {
            return (int) $payload['amount'];
        }

        return null;
    }

    private function verifySignature(string $payload, string $signatureHeader, string $secret): bool
    {
        $pairs = explode(',', $signatureHeader);
        $timestamp = null;
        $signatures = [];

        foreach ($pairs as $pair) {
            [$key, $value] = array_map('trim', explode('=', $pair, 2));
            if ($key === 't') {
                $timestamp = $value;
            }
            if ($key === 'v1') {
                $signatures[] = $value;
            }
        }

        if (! $timestamp || $signatures === []) {
            return false;
        }

        $signedPayload = $timestamp.'.'.$payload;
        $computed = hash_hmac('sha256', $signedPayload, $secret);

        if (! in_array($computed, $signatures, true)) {
            return false;
        }

        $tolerance = 300;
        if (abs(time() - (int) $timestamp) > $tolerance) {
            return false;
        }

        return true;
    }
}
