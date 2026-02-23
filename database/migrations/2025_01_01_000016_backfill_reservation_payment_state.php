<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('reservations')
            ->select(['id', 'status', 'total', 'total_paid', 'is_paid'])
            ->orderBy('id')
            ->chunkById(200, function (Collection $reservations): void {
                $reservationIds = $reservations->pluck('id')->all();
                $paidTotals = DB::table('payments')
                    ->selectRaw('reservation_id, COALESCE(SUM(amount), 0) as paid_total')
                    ->whereIn('reservation_id', $reservationIds)
                    ->where('status', 'paid')
                    ->groupBy('reservation_id')
                    ->pluck('paid_total', 'reservation_id');
                $authorizedTotals = DB::table('payments')
                    ->selectRaw('reservation_id, COALESCE(SUM(amount), 0) as authorized_total')
                    ->whereIn('reservation_id', $reservationIds)
                    ->where('status', 'authorized')
                    ->groupBy('reservation_id')
                    ->pluck('authorized_total', 'reservation_id');

                $now = now();

                foreach ($reservations as $reservation) {
                    $paidTotal = round((float) ($paidTotals[$reservation->id] ?? 0), 2);
                    $authorizedTotal = round((float) ($authorizedTotals[$reservation->id] ?? 0), 2);
                    $coveredTotal = round($paidTotal + $authorizedTotal, 2);
                    $reservationTotal = (float) $reservation->total;
                    $currentTotalPaid = round((float) $reservation->total_paid, 2);
                    $isPaid = $paidTotal >= $reservationTotal;
                    $updates = [];

                    if ($currentTotalPaid !== $paidTotal) {
                        $updates['total_paid'] = $paidTotal;
                    }

                    if ((bool) $reservation->is_paid !== $isPaid) {
                        $updates['is_paid'] = $isPaid;
                    }

                    if ($reservation->status === 'pending' && $coveredTotal <= 0) {
                        $updates['status'] = 'awaiting_payment';
                    } elseif ($reservation->status === 'awaiting_payment' && $coveredTotal > 0) {
                        $updates['status'] = 'pending';
                    }

                    if ($updates === []) {
                        continue;
                    }

                    $updates['updated_at'] = $now;

                    DB::table('reservations')
                        ->where('id', $reservation->id)
                        ->update($updates);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Intentionally left empty: backfill data migration.
    }
};
