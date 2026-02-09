<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Payment reminder</title>
</head>
<body>
    @php
        $isEnglish = $locale === 'en';
        $label = $daysToCheckIn === 14
            ? ($isEnglish ? 'Two weeks to check-in' : 'Mancano due settimane al check-in')
            : ($isEnglish ? 'One week to check-in' : 'Manca una settimana al check-in');
        $message = $isEnglish
            ? 'Please complete the payment before the deadline.'
            : 'Completa il pagamento entro la scadenza.';
    @endphp

    <p>{{ $label }}</p>
    <p>{{ $message }}</p>
    <p>
        {{ $isEnglish ? 'Reservation' : 'Prenotazione' }} #{{ $reservation->id }}
    </p>
</body>
</html>
