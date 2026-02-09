<?php

namespace App\Mail;

use App\Models\Reservation;
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
        public readonly ?string $locale = null
    ) {
    }

    public function build(): self
    {
        $isEnglish = $this->locale === 'en';
        $subject = $this->daysToCheckIn === 14
            ? 'Promemoria pagamento prenotazione'
            : 'Pagamento prenotazione in scadenza';

        if ($isEnglish) {
            $subject = $this->daysToCheckIn === 14
                ? 'Booking payment reminder'
                : 'Booking payment due soon';
        }

        return $this->subject($subject)
            ->view('emails.payment-reminder');
    }
}
