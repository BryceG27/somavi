<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Reservation;
use App\Models\UserGroup;
use App\Services\StripeCheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

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

    public function webhook(Request $request): Response
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

        if (($event['type'] ?? '') === 'checkout.session.completed') {
            $session = $event['data']['object'] ?? [];
            $metadata = $session['metadata'] ?? [];
            $paymentId = (int) ($metadata['payment_id'] ?? 0);

            $payment = Payment::query()->with('reservation')->find($paymentId);

            if ($payment) {
                $this->applyAuthorization($payment, $session);
            }
        }

        if (($event['type'] ?? '') === 'payment_intent.succeeded') {
            $intent = $event['data']['object'] ?? [];
            $metadata = $intent['metadata'] ?? [];
            $paymentId = (int) ($metadata['payment_id'] ?? 0);

            $payment = Payment::query()->with('reservation')->find($paymentId);

            if ($payment) {
                $this->applyPayment($payment, $intent);
            }
        }

        if (($event['type'] ?? '') === 'payment_intent.canceled') {
            $intent = $event['data']['object'] ?? [];
            $metadata = $intent['metadata'] ?? [];
            $paymentId = (int) ($metadata['payment_id'] ?? 0);

            $payment = Payment::query()->find($paymentId);

            if ($payment) {
                $payment->forceFill([
                    'stripe_payment_intent_id' => $intent['id'] ?? $payment->stripe_payment_intent_id,
                    'status' => Payment::STATUS_VOIDED,
                ])->save();
            }
        }

        return response('ok', 200);
    }

    private function applyAuthorization(Payment $payment, array $session): void
    {
        $payment->forceFill([
            'stripe_checkout_session_id' => $session['id'] ?? $payment->stripe_checkout_session_id,
            'stripe_payment_intent_id' => $session['payment_intent'] ?? $payment->stripe_payment_intent_id,
            'status' => Payment::STATUS_AUTHORIZED,
        ])->save();
    }

    private function applyPayment(Payment $payment, array $session): void
    {
        $paymentIntentId = $session['payment_intent'] ?? $session['id'] ?? null;
        $sessionId = ($session['object'] ?? null) === 'checkout.session'
            ? ($session['id'] ?? null)
            : null;

        $payment->forceFill([
            'stripe_checkout_session_id' => $sessionId ?? $payment->stripe_checkout_session_id,
            'stripe_payment_intent_id' => $paymentIntentId ?? $payment->stripe_payment_intent_id,
            'status' => Payment::STATUS_PAID,
            'paid_at' => Carbon::now(),
        ])->save();

        $reservation = $payment->reservation;

        if (! $reservation) {
            return;
        }

        $totalPaid = (float) $reservation->payments()->where('status', Payment::STATUS_PAID)->sum('amount');
        $reservation->forceFill([
            'total_paid' => $totalPaid,
            'is_paid' => $totalPaid >= (float) $reservation->total,
        ])->save();
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
