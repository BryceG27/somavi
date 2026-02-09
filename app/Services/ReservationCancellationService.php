<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Reservation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReservationCancellationService
{
    public function cancelByCustomer(Reservation $reservation): void
    {
        $this->cancel($reservation, Reservation::CANCELLED_BY_CUSTOMER, 0.30);
    }

    public function cancelByAdmin(Reservation $reservation): void
    {
        $this->cancel($reservation, Reservation::CANCELLED_BY_ADMIN, 1.00);
    }

    private function cancel(Reservation $reservation, string $cancelledBy, float $refundRate): void
    {
        if ($reservation->status === Reservation::STATUS_CANCELLED) {
            return;
        }

        DB::transaction(function () use ($reservation, $cancelledBy, $refundRate): void {
            $maxRefund = round(((float) $reservation->total) * $refundRate, 2);
            $refundAmount = min((float) $reservation->total_paid, $maxRefund);
            $refundAmount = round($refundAmount, 2);
            $remaining = $refundAmount;

            if ($remaining > 0) {
                $payments = $reservation->payments()
                    ->where('status', Payment::STATUS_PAID)
                    ->orderBy('paid_at')
                    ->get();

                foreach ($payments as $payment) {
                    if ($remaining <= 0) {
                        break;
                    }

                    if (! $payment->stripe_payment_intent_id) {
                        continue;
                    }

                    $alreadyRefunded = (float) $payment->refund_amount;
                    $refundable = max(0, (float) $payment->amount - $alreadyRefunded);
                    $amountToRefund = min($remaining, $refundable);

                    if ($amountToRefund <= 0) {
                        continue;
                    }

                    $refundId = app(StripeRefundService::class)->refund(
                        $payment->stripe_payment_intent_id,
                        $amountToRefund
                    );

                    $payment->forceFill([
                        'refund_amount' => $alreadyRefunded + $amountToRefund,
                        'refunded_at' => Carbon::now(),
                        'stripe_refund_id' => $refundId,
                        'status' => ($alreadyRefunded + $amountToRefund) >= (float) $payment->amount
                            ? Payment::STATUS_REFUNDED
                            : $payment->status,
                    ])->save();

                    $remaining = round($remaining - $amountToRefund, 2);
                }
            }

            $reservation->forceFill([
                'status' => Reservation::STATUS_CANCELLED,
                'cancelled_by' => $cancelledBy,
            ])->save();
        });
    }
}
