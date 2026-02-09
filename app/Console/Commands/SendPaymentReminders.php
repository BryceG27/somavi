<?php

namespace App\Console\Commands;

use App\Mail\PaymentReminderMail;
use App\Models\Reservation;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

class SendPaymentReminders extends Command
{
    protected $signature = 'reservations:send-payment-reminders';

    protected $description = 'Send payment reminders 14 and 7 days before check-in.';

    public function handle(): int
    {
        $today = Carbon::today();

        $this->sendForDate($today->copy()->addDays(14), 14, 'reminder_14_sent_at');
        $this->sendForDate($today->copy()->addDays(7), 7, 'reminder_7_sent_at');

        return self::SUCCESS;
    }

    private function sendForDate(Carbon $startDate, int $daysToCheckIn, string $flagColumn): void
    {
        $reservations = Reservation::query()
            ->with(['customer', 'payments'])
            ->whereDate('start_date', $startDate)
            ->where('total_paid', '<', DB::raw('total'))
            ->whereNull($flagColumn)
            ->get();

        foreach ($reservations as $reservation) {
            $customer = $reservation->customer;

            if (! $customer?->email) {
                continue;
            }

            $locale = $reservation->payments()
                ->orderBy('id')
                ->value('locale');

            Mail::to($customer->email)->send(
                new PaymentReminderMail($reservation, $daysToCheckIn, $locale)
            );

            $reservation->forceFill([$flagColumn => Carbon::now()])->save();
        }
    }
}
