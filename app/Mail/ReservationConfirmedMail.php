<?php

namespace App\Mail;

use App\Models\Reservation;
use App\Support\LocalePreference;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReservationConfirmedMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly Reservation $reservation,
        public readonly ?string $preferredLocale = null
    ) {
    }

    public function build(): self
    {
        $locale = LocalePreference::normalize($this->preferredLocale);
        $isEnglish = $locale === 'en';
        $subject = $isEnglish
            ? 'Reservation confirmed'
            : 'Prenotazione confermata';

        return $this->subject($subject)
            ->view('emails.reservation-confirmed', [
                'locale' => $locale,
            ]);
    }
}
