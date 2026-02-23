<?php

namespace App\Services\ExternalCalendar;

use Carbon\CarbonImmutable;
use Throwable;

class IcsCalendarParser
{
    /**
     * @return list<array{uid:string,start_date:string,end_date:string}>
     */
    public function parseEvents(string $ics): array
    {
        $events = [];
        $eventLines = [];
        $isInEvent = false;

        foreach ($this->unfoldLines($ics) as $line) {
            $upperLine = strtoupper($line);

            if ($upperLine === 'BEGIN:VEVENT') {
                $isInEvent = true;
                $eventLines = [];

                continue;
            }

            if ($upperLine === 'END:VEVENT') {
                if ($isInEvent) {
                    $event = $this->parseEvent($eventLines);

                    if ($event !== null) {
                        $events[] = $event;
                    }
                }

                $isInEvent = false;
                $eventLines = [];

                continue;
            }

            if ($isInEvent) {
                $eventLines[] = $line;
            }
        }

        return $events;
    }

    /**
     * @return list<string>
     */
    private function unfoldLines(string $ics): array
    {
        $normalized = str_replace(["\r\n", "\r"], "\n", $ics);
        $lines = explode("\n", $normalized);
        $unfolded = [];

        foreach ($lines as $line) {
            if ($line === '') {
                continue;
            }

            if (($line[0] ?? '') === ' ' || ($line[0] ?? '') === "\t") {
                $lastIndex = count($unfolded) - 1;

                if ($lastIndex >= 0) {
                    $unfolded[$lastIndex] .= substr($line, 1);
                }

                continue;
            }

            $unfolded[] = $line;
        }

        return $unfolded;
    }

    /**
     * @param list<string> $eventLines
     * @return array{uid:string,start_date:string,end_date:string}|null
     */
    private function parseEvent(array $eventLines): ?array
    {
        $properties = [];

        foreach ($eventLines as $line) {
            $separator = strpos($line, ':');

            if ($separator === false) {
                continue;
            }

            $rawName = strtoupper(substr($line, 0, $separator));
            $value = trim(substr($line, $separator + 1));
            $name = strtoupper(strtok($rawName, ';') ?: $rawName);

            if ($value === '') {
                continue;
            }

            $properties[$name] = [
                'raw_name' => $rawName,
                'value' => $value,
            ];
        }

        $status = strtoupper((string) ($properties['STATUS']['value'] ?? ''));

        if ($status === 'CANCELLED') {
            return null;
        }

        $startProperty = $properties['DTSTART'] ?? null;

        if (! $startProperty) {
            return null;
        }

        $start = $this->parseDateProperty(
            $startProperty['raw_name'],
            $startProperty['value']
        );

        if (! $start) {
            return null;
        }

        $endProperty = $properties['DTEND'] ?? null;
        $end = $endProperty
            ? $this->parseDateProperty($endProperty['raw_name'], $endProperty['value'])
            : $start->addDay();

        if (! $end || $end->lessThanOrEqualTo($start)) {
            $end = $start->addDay();
        }

        $timezone = $this->resolveTimezone();
        $startDate = $start->setTimezone($timezone)->toDateString();
        $endDate = $end->setTimezone($timezone)->toDateString();

        if ($endDate <= $startDate) {
            $endDate = $start->addDay()->setTimezone($timezone)->toDateString();
        }

        $uid = trim((string) ($properties['UID']['value'] ?? ''));
        $recurrenceId = trim((string) ($properties['RECURRENCE-ID']['value'] ?? ''));

        if ($uid === '') {
            $uid = sha1($startDate.'|'.$endDate.'|'.($properties['SUMMARY']['value'] ?? ''));
        }

        if ($recurrenceId !== '') {
            $uid .= '|'.$recurrenceId;
        }

        return [
            'uid' => $uid,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];
    }

    private function parseDateProperty(string $rawName, string $value): ?CarbonImmutable
    {
        $timezone = $this->extractTimezone($rawName) ?? $this->resolveTimezone();
        $isDate = str_contains($rawName, 'VALUE=DATE') || preg_match('/^\d{8}$/', $value) === 1;

        if ($isDate) {
            $parsed = $this->safeParse('Ymd', $value, $timezone);

            return $parsed?->startOfDay();
        }

        if (preg_match('/^\d{8}T\d{6}Z$/', $value) === 1) {
            return $this->safeParse('Ymd\\THis\\Z', $value, 'UTC');
        }

        if (preg_match('/^\d{8}T\d{6}$/', $value) === 1) {
            return $this->safeParse('Ymd\\THis', $value, $timezone);
        }

        return null;
    }

    private function extractTimezone(string $rawName): ?string
    {
        if (preg_match('/TZID=([^;:]+)/i', $rawName, $matches) !== 1) {
            return null;
        }

        return trim($matches[1]);
    }

    private function safeParse(string $format, string $value, string $timezone): ?CarbonImmutable
    {
        try {
            $parsed = CarbonImmutable::createFromFormat($format, $value, $timezone);
        } catch (Throwable) {
            return null;
        }

        if (! $parsed instanceof CarbonImmutable) {
            return null;
        }

        return $parsed;
    }

    private function resolveTimezone(): string
    {
        try {
            $timezone = (string) config('app.timezone', 'UTC');
        } catch (Throwable) {
            $timezone = '';
        }

        return $timezone !== '' ? $timezone : 'UTC';
    }
}
