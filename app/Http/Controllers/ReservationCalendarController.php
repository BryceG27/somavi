<?php

namespace App\Http\Controllers;

use App\Models\Apartment;
use App\Models\Reservation;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class ReservationCalendarController extends Controller
{
    public function apartmentReservations(Apartment $apartment): Response
    {
        $reservations = $apartment->reservations()
            ->whereIn('status', [
                Reservation::STATUS_AWAITING_PAYMENT,
                Reservation::STATUS_PENDING,
                Reservation::STATUS_CONFIRMED,
            ])
            ->whereDate('end_date', '>', now()->toDateString())
            ->orderBy('start_date')
            ->orderBy('id')
            ->get(['id', 'start_date', 'end_date', 'status', 'created_at', 'updated_at']);

        $filename = sprintf('somavi-apartment-%d-reservations.ics', $apartment->id);

        return response($this->buildCalendar($apartment, $reservations), 200, [
            'Content-Type' => 'text/calendar; charset=UTF-8',
            'Content-Disposition' => sprintf('inline; filename="%s"', $filename),
            'Cache-Control' => 'no-cache, must-revalidate',
        ]);
    }

    /**
     * @param Collection<int, Reservation> $reservations
     */
    private function buildCalendar(Apartment $apartment, Collection $reservations): string
    {
        $calendarName = $this->escapeIcalText((string) ($apartment->name_it ?: $apartment->name_en ?: 'Somavi Reservations'));
        $uidHost = $this->uidHost();

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Somavi//Reservations Feed//EN',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'X-WR-CALNAME:'.$calendarName,
        ];

        foreach ($reservations as $reservation) {
            $startDate = $reservation->start_date?->format('Ymd');
            $endDate = $reservation->end_date?->format('Ymd');

            if (! $startDate || ! $endDate) {
                continue;
            }

            $dtstampSource = $reservation->updated_at ?? $reservation->created_at ?? now();
            $dtstamp = $dtstampSource->utc()->format('Ymd\THis\Z');
            $uid = sprintf('reservation-%d@%s', $reservation->id, $uidHost);
            $statusLabel = $this->reservationStatusLabel((string) $reservation->status);

            $lines[] = 'BEGIN:VEVENT';
            $lines[] = 'UID:'.$uid;
            $lines[] = 'DTSTAMP:'.$dtstamp;
            $lines[] = 'DTSTART;VALUE=DATE:'.$startDate;
            $lines[] = 'DTEND;VALUE=DATE:'.$endDate;
            $lines[] = 'SUMMARY:'.$this->escapeIcalText('Somavi Reservation');
            $lines[] = 'DESCRIPTION:'.$this->escapeIcalText("Reservation #{$reservation->id} - {$statusLabel}");
            $lines[] = 'END:VEVENT';
        }

        $lines[] = 'END:VCALENDAR';

        return implode("\r\n", $lines)."\r\n";
    }

    private function uidHost(): string
    {
        $configuredHost = parse_url((string) config('app.url'), PHP_URL_HOST);
        $requestHost = request()->getHost();
        $host = $configuredHost ?: $requestHost ?: 'somavi.local';

        return preg_replace('/[^a-z0-9.-]+/i', '', (string) $host) ?: 'somavi.local';
    }

    private function reservationStatusLabel(string $status): string
    {
        return match ($status) {
            Reservation::STATUS_AWAITING_PAYMENT => 'awaiting_payment',
            Reservation::STATUS_PENDING => 'pending',
            Reservation::STATUS_CONFIRMED => 'confirmed',
            default => $status,
        };
    }

    private function escapeIcalText(string $value): string
    {
        return str_replace(
            ['\\', ';', ',', "\r\n", "\n", "\r"],
            ['\\\\', '\;', '\,', '\n', '\n', '\n'],
            $value
        );
    }
}
