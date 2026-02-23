<?php

use App\Services\ExternalCalendar\IcsCalendarParser;

it('parses active events and skips cancelled events', function () {
    $ics = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
UID:airbnb-1
DTSTART;VALUE=DATE:20260210
DTEND;VALUE=DATE:20260215
SUMMARY:Airbnb Reservation
END:VEVENT
BEGIN:VEVENT
UID:booking-2
DTSTART:20260220T150000Z
DTEND:20260222T100000Z
SUMMARY:Booking Reservation
END:VEVENT
BEGIN:VEVENT
UID:cancelled-3
DTSTART;VALUE=DATE:20260225
DTEND;VALUE=DATE:20260227
STATUS:CANCELLED
END:VEVENT
END:VCALENDAR
ICS;

    $events = app(IcsCalendarParser::class)->parseEvents($ics);

    expect($events)->toHaveCount(2)
        ->and($events[0])->toMatchArray([
            'uid' => 'airbnb-1',
            'start_date' => '2026-02-10',
            'end_date' => '2026-02-15',
        ])
        ->and($events[1])->toMatchArray([
            'uid' => 'booking-2',
            'start_date' => '2026-02-20',
            'end_date' => '2026-02-22',
        ]);
});

it('unfolds multiline properties', function () {
    $ics = <<<ICS
BEGIN:VCALENDAR
BEGIN:VEVENT
UID:vrbo-abcde
 12345
DTSTART;VALUE=DATE:20260301
DTEND;VALUE=DATE:20260305
END:VEVENT
END:VCALENDAR
ICS;

    $events = app(IcsCalendarParser::class)->parseEvents($ics);

    expect($events)->toHaveCount(1)
        ->and($events[0]['uid'])->toBe('vrbo-abcde12345');
});
