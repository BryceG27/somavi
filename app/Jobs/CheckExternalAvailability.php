<?php

namespace App\Jobs;

use App\Models\Payment;
use App\Models\Reservation;
use App\Services\ReservationCancellationService;
use App\Services\StripePaymentIntentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class CheckExternalAvailability implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(private readonly int $reservationId)
    {
    }

    public function handle(): void
    {
        $reservation = Reservation::query()->with('apartment')->find($this->reservationId);

        if (! $reservation) {
            return;
        }

        // TODO: integrare verifica asincrona disponibilita con portali esterni (Airbnb, Booking, Vrbo).
        Log::info('External availability check queued.', [
            'reservation_id' => $reservation->id,
            'apartment_id' => $reservation->apartment_id,
            'start_date' => $reservation->start_date?->toDateString(),
            'end_date' => $reservation->end_date?->toDateString(),
        ]);

        if ($reservation->status !== Reservation::STATUS_PENDING) {
            return;
        }

        $payment = $reservation->payments()
            ->where('status', Payment::STATUS_AUTHORIZED)
            ->orderByRaw("case when step = 'deposit' then 0 when step = 'balance' then 1 else 2 end")
            ->first();

        if (! $payment || ! $payment->stripe_payment_intent_id) {
            return;
        }

        $availability = $this->resolveAvailability();

        if ($availability === null) {
            return;
        }

        if (! $availability) {
            app(ReservationCancellationService::class)->cancelByAdmin($reservation);
            return;
        }

        app(StripePaymentIntentService::class)->capture($payment->stripe_payment_intent_id);

        $payment->forceFill([
            'status' => Payment::STATUS_PAID,
            'paid_at' => Carbon::now(),
        ])->save();

        $totalPaid = (float) $reservation->payments()->where('status', Payment::STATUS_PAID)->sum('amount');
        $reservation->forceFill([
            'total_paid' => $totalPaid,
            'is_paid' => $totalPaid >= (float) $reservation->total,
            'status' => Reservation::STATUS_CONFIRMED,
        ])->save();
    }

    private function resolveAvailability(): ?bool
    {
        $mode = config('services.availability.mode', 'manual');

        return match ($mode) {
            'assume_available' => true,
            'assume_unavailable' => false,
            default => null,
        };
    }
}
