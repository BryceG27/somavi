<?php

namespace App\Services\ExternalCalendar;

use App\Models\Apartment;
use App\Models\BlockedDate;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class ExternalCalendarSyncService
{
    /**
     * @var array<string, string>
     */
    private const PROVIDER_COLUMNS = [
        'airbnb' => 'airbnb_ical_url',
        'booking' => 'booking_ical_url',
        'vrbo' => 'vrbo_ical_url',
    ];

    public function __construct(private readonly IcsCalendarParser $parser)
    {
    }

    /**
     * @return array<string, array{added:int,updated:int,deleted:int,error:string|null}>
     */
    public function syncApartment(Apartment $apartment): array
    {
        $results = [];

        foreach (self::PROVIDER_COLUMNS as $provider => $column) {
            $icalUrl = trim((string) ($apartment->{$column} ?? ''));

            if ($icalUrl === '') {
                continue;
            }

            $results[$provider] = $this->syncProvider($apartment, $provider, $icalUrl);
        }

        return $results;
    }

    /**
     * @return array{apartments:int,providers:int,added:int,updated:int,deleted:int,errors:int}
     */
    public function syncAllApartments(): array
    {
        $apartments = Apartment::query()
            ->where(function ($query) {
                foreach (self::PROVIDER_COLUMNS as $column) {
                    $query->orWhere(function ($providerQuery) use ($column) {
                        $providerQuery
                            ->whereNotNull($column)
                            ->where($column, '!=', '');
                    });
                }
            })
            ->get();

        $summary = [
            'apartments' => $apartments->count(),
            'providers' => 0,
            'added' => 0,
            'updated' => 0,
            'deleted' => 0,
            'errors' => 0,
        ];

        foreach ($apartments as $apartment) {
            $results = $this->syncApartment($apartment);

            foreach ($results as $result) {
                $summary['providers']++;
                $summary['added'] += $result['added'];
                $summary['updated'] += $result['updated'];
                $summary['deleted'] += $result['deleted'];

                if ($result['error'] !== null) {
                    $summary['errors']++;
                }
            }
        }

        return $summary;
    }

    /**
     * @return array{added:int,updated:int,deleted:int,error:string|null}
     */
    private function syncProvider(Apartment $apartment, string $provider, string $icalUrl): array
    {
        try {
            $response = Http::timeout((int) config('services.availability.ics_timeout_seconds', 10))
                ->accept('text/calendar,text/plain,*/*')
                ->get($icalUrl);

            if (! $response->successful()) {
                throw new \RuntimeException('HTTP status '.$response->status());
            }

            $events = collect($this->parser->parseEvents($response->body()))
                ->unique('uid')
                ->values();

            return $this->syncBlockedDates($apartment, $provider, $events);
        } catch (Throwable $exception) {
            Log::warning('Unable to sync external ICS calendar.', [
                'apartment_id' => $apartment->id,
                'provider' => $provider,
                'ical_url' => $icalUrl,
                'error' => $exception->getMessage(),
            ]);

            return [
                'added' => 0,
                'updated' => 0,
                'deleted' => 0,
                'error' => $exception->getMessage(),
            ];
        }
    }

    /**
     * @param Collection<int, array{uid:string,start_date:string,end_date:string}> $events
     * @return array{added:int,updated:int,deleted:int,error:string|null}
     */
    private function syncBlockedDates(Apartment $apartment, string $provider, Collection $events): array
    {
        $managedPrefix = 'ical:'.$provider.':';

        return DB::transaction(function () use ($apartment, $events, $managedPrefix): array {
            $existing = BlockedDate::query()
                ->where('apartment_id', $apartment->id)
                ->where('reason', 'like', $managedPrefix.'%')
                ->get()
                ->keyBy('reason');

            $added = 0;
            $updated = 0;
            $seenReasons = [];

            foreach ($events as $event) {
                $reason = $managedPrefix.sha1($event['uid']);
                $seenReasons[] = $reason;

                /** @var BlockedDate|null $blockedDate */
                $blockedDate = $existing->get($reason);

                if (! $blockedDate) {
                    BlockedDate::query()->create([
                        'apartment_id' => $apartment->id,
                        'start_date' => $event['start_date'],
                        'end_date' => $event['end_date'],
                        'reason' => $reason,
                    ]);

                    $added++;

                    continue;
                }

                if (
                    $blockedDate->start_date?->toDateString() !== $event['start_date'] ||
                    $blockedDate->end_date?->toDateString() !== $event['end_date']
                ) {
                    $blockedDate->forceFill([
                        'start_date' => $event['start_date'],
                        'end_date' => $event['end_date'],
                    ])->save();

                    $updated++;
                }
            }

            $staleReasons = $existing
                ->keys()
                ->reject(fn (string $reason) => in_array($reason, $seenReasons, true));

            $deleted = 0;

            if ($staleReasons->isNotEmpty()) {
                $deleted = BlockedDate::query()
                    ->where('apartment_id', $apartment->id)
                    ->whereIn('reason', $staleReasons->all())
                    ->delete();
            }

            return [
                'added' => $added,
                'updated' => $updated,
                'deleted' => $deleted,
                'error' => null,
            ];
        });
    }
}
