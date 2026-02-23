<?php

namespace App\Jobs;

use App\Models\BlockedDate;
use App\Models\Payment;
use App\Models\Reservation;
use App\Services\ExternalCalendar\ExternalCalendarSyncService;
use App\Services\ReservationCancellationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

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
        $reservation = Reservation::query()
            ->with(['apartment', 'payments'])
            ->find($this->reservationId);

        if (! $reservation) {
            return;
        }

        Log::info('External availability check queued.', [
            'reservation_id' => $reservation->id,
            'apartment_id' => $reservation->apartment_id,
            'start_date' => $reservation->start_date?->toDateString(),
            'end_date' => $reservation->end_date?->toDateString(),
        ]);

        if ($reservation->status !== Reservation::STATUS_PENDING) {
            return;
        }

        $hasPaidPayment = $reservation->payments
            ->contains(fn (Payment $payment) => $payment->status === Payment::STATUS_PAID);

        if (! $hasPaidPayment) {
            return;
        }

        $availability = $this->resolveAvailability($reservation);

        if ($availability === null) {
            return;
        }

        if (! $availability) {
            app(ReservationCancellationService::class)->cancelByAdmin($reservation);
            return;
        }

        $reservation->forceFill([
            'status' => Reservation::STATUS_CONFIRMED,
        ])->save();
    }

    private function resolveAvailability(Reservation $reservation): ?bool
    {
        $mode = config('services.availability.mode', 'ics_sync');

        return match ($mode) {
            'assume_available' => true,
            'assume_unavailable' => false,
            'ics_sync' => $this->resolveIcsAvailability($reservation),
            default => null,
        };
    }

    private function resolveIcsAvailability(Reservation $reservation): ?bool
    {
        if (! $reservation->apartment || ! $reservation->start_date || ! $reservation->end_date) {
            return null;
        }

        try {
            app(ExternalCalendarSyncService::class)->syncApartment($reservation->apartment);
        } catch (Throwable $exception) {
            Log::warning('External ICS sync failed while checking availability.', [
                'reservation_id' => $reservation->id,
                'apartment_id' => $reservation->apartment_id,
                'error' => $exception->getMessage(),
            ]);

            return null;
        }

        $isBlocked = BlockedDate::query()
            ->where('apartment_id', $reservation->apartment_id)
            ->where('start_date', '<', $reservation->end_date)
            ->where('end_date', '>', $reservation->start_date)
            ->exists();

        return ! $isBlocked;
    }
}
