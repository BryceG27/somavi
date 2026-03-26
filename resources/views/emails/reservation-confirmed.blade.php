<!DOCTYPE html>
<html lang="{{ $locale === 'en' ? 'en' : 'it' }}">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Reservation confirmed</title>
</head>
<body>
    @php
        $isEnglish = $locale === 'en';
        $apartmentName = $isEnglish
            ? ($reservation->apartment?->name_en ?: $reservation->apartment?->name_it)
            : ($reservation->apartment?->name_it ?: $reservation->apartment?->name_en);
        $startDate = $reservation->start_date?->toDateString();
        $endDate = $reservation->end_date?->toDateString();
    @endphp

    <p>
        {{ $isEnglish ? 'Your reservation is confirmed.' : 'La tua prenotazione e confermata.' }}
    </p>
    <p>
        {{ $isEnglish ? 'Reservation' : 'Prenotazione' }} #{{ $reservation->id }}
    </p>
    @if ($apartmentName)
        <p>{{ $apartmentName }}</p>
    @endif
    @if ($startDate && $endDate)
        <p>{{ $startDate }} - {{ $endDate }}</p>
    @endif
</body>
</html>
