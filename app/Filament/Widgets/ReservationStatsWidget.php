<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use App\Models\Reservation;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ReservationStatsWidget extends StatsOverviewWidget
{
    /**
     * @return array<int, Stat>
     */
    protected function getStats(): array
    {
        $today = Carbon::today();

        $totalReservations = Reservation::query()->count();
        $activeReservations = Reservation::query()
            ->where('status', Reservation::STATUS_CONFIRMED)
            ->whereDate('end_date', '>=', $today)
            ->count();
        $awaitingPaymentReservations = Reservation::query()
            ->where('status', Reservation::STATUS_AWAITING_PAYMENT)
            ->count();
        $pendingReservations = Reservation::query()
            ->where('status', Reservation::STATUS_PENDING)
            ->count();
        $cancelledReservations = Reservation::query()
            ->where('status', Reservation::STATUS_CANCELLED)
            ->count();
        $customersCount = Customer::query()->count();

        return [
            Stat::make('Prenotazioni totali', $totalReservations),
            Stat::make('Prenotazioni attive', $activeReservations),
            Stat::make('In attesa pagamento', $awaitingPaymentReservations),
            Stat::make('In verifica disponibilita', $pendingReservations),
            Stat::make('Prenotazioni cancellate', $cancelledReservations),
            Stat::make('Clienti', $customersCount),
        ];
    }
}
