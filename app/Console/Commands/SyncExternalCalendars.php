<?php

namespace App\Console\Commands;

use App\Models\Apartment;
use App\Services\ExternalCalendar\ExternalCalendarSyncService;
use Illuminate\Console\Command;

class SyncExternalCalendars extends Command
{
    protected $signature = 'availability:sync-ics {apartment_id? : Sync only one apartment}';

    protected $description = 'Sync external iCal feeds (Airbnb, Booking, Vrbo) into blocked dates.';

    public function handle(ExternalCalendarSyncService $syncService): int
    {
        $apartmentId = $this->argument('apartment_id');

        if ($apartmentId !== null) {
            $apartment = Apartment::query()->find((int) $apartmentId);

            if (! $apartment) {
                $this->error('Apartment not found.');

                return self::FAILURE;
            }

            $results = $syncService->syncApartment($apartment);

            $this->info('Sync completed for apartment #'.$apartment->id.'.');

            foreach ($results as $provider => $result) {
                $line = sprintf(
                    '%s: +%d ~%d -%d%s',
                    $provider,
                    $result['added'],
                    $result['updated'],
                    $result['deleted'],
                    $result['error'] ? ' (error: '.$result['error'].')' : ''
                );

                $this->line($line);
            }

            return self::SUCCESS;
        }

        $summary = $syncService->syncAllApartments();

        $this->info(sprintf(
            'Sync completed. apartments=%d providers=%d added=%d updated=%d deleted=%d errors=%d',
            $summary['apartments'],
            $summary['providers'],
            $summary['added'],
            $summary['updated'],
            $summary['deleted'],
            $summary['errors'],
        ));

        return self::SUCCESS;
    }
}
