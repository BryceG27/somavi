<?php

namespace App\Http\Controllers;

use App\Services\ExternalCalendar\ExternalCalendarSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class InternalCronController extends Controller
{
    public function syncIcs(Request $request, ExternalCalendarSyncService $syncService): JsonResponse
    {
        $expectedToken = trim((string) config('services.availability.cron_token', ''));
        $providedToken = trim((string) (
            $request->header('X-CRON-TOKEN')
            ?? $request->query('token')
            ?? $request->input('token')
            ?? ''
        ));

        if ($expectedToken === '' || ! hash_equals($expectedToken, $providedToken)) {
            return response()->json([
                'ok' => false,
                'message' => 'Unauthorized.',
            ], 401);
        }

        $lockSeconds = max(60, (int) config('services.availability.cron_lock_seconds', 3300));
        $lock = Cache::lock('availability:sync-ics:http', $lockSeconds);

        if (! $lock->get()) {
            return response()->json([
                'ok' => true,
                'status' => 'already_running',
            ], 202);
        }

        try {
            $summary = $syncService->syncAllApartments();

            return response()->json([
                'ok' => true,
                'status' => 'completed',
                'summary' => $summary,
            ]);
        } catch (Throwable $exception) {
            Log::error('HTTP ICS sync failed.', [
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'ok' => false,
                'message' => 'Sync failed.',
            ], 500);
        } finally {
            optional($lock)->release();
        }
    }
}
