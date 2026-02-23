<?php

namespace App\Mail;

use App\Models\Reservation;
use App\Support\LocalePreference;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentReminderMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly Reservation $reservation,
        public readonly int $daysToCheckIn,
        public readonly ?string $preferredLocale = null
    ) {
    }

    public function build(): self
    {
        $locale = LocalePreference::normalize($this->preferredLocale);
        $isEnglish = $locale === 'en';
        $subject = $this->daysToCheckIn === 14
            ? 'Promemoria pagamento prenotazione'
            : 'Pagamento prenotazione in scadenza';

        if ($isEnglish) {
            $subject = $this->daysToCheckIn === 14
                ? 'Booking payment reminder'
                : 'Booking payment due soon';
        }

        return $this->subject($subject)
            ->view('emails.payment-reminder', [
                'locale' => $locale,
            ]);
    }
}
