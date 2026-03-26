<?php

namespace App\Console\Commands;

use App\Jobs\CheckExternalAvailability;
use App\Models\Payment;
use App\Models\Reservation;
use Illuminate\Console\Command;

class VerifyPendingReservationsAvailability extends Command
{
    protected $signature = 'reservations:verify-pending-availability {--limit=200 : Max pending reservations to verify}';

    protected $description = 'Verify pending reservations with at least one paid payment against external availability.';

    public function handle(): int
    {
        $limit = max(1, (int) $this->option('limit'));

        $reservationIds = Reservation::query()
            ->where('status', Reservation::STATUS_PENDING)
            ->whereHas('payments', function ($query): void {
                $query->where('status', Payment::STATUS_PAID);
            })
            ->orderBy('id')
            ->limit($limit)
            ->pluck('id');

        $processed = 0;
        $confirmed = 0;
        $cancelled = 0;
        $unchanged = 0;

        foreach ($reservationIds as $reservationId) {
            CheckExternalAvailability::dispatchSync((int) $reservationId);

            $status = (string) Reservation::query()
                ->whereKey((int) $reservationId)
                ->value('status');

            $processed++;

            if ($status === Reservation::STATUS_CONFIRMED) {
                $confirmed++;
                continue;
            }

            if ($status === Reservation::STATUS_CANCELLED) {
                $cancelled++;
                continue;
            }

            $unchanged++;
        }

        $this->info(sprintf(
            'Pending reservations check completed. processed=%d confirmed=%d cancelled=%d unchanged=%d',
            $processed,
            $confirmed,
            $cancelled,
            $unchanged,
        ));

        return self::SUCCESS;
    }
}
