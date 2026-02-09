<?php

namespace App\Jobs;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
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
    }
}
