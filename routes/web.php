<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InternalCronController;
use App\Http\Controllers\ReservationCalendarController;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\WebController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('home');
Route::get('/robots.txt', [DashboardController::class, 'robots'])->name('seo.robots');
Route::get('/sitemap.xml', [DashboardController::class, 'sitemap'])->name('seo.sitemap');

Route::get('/login', [WebController::class, 'showLogin'])->name('login');
Route::post('/login', [WebController::class, 'login'])->name('login.store');
Route::get('/register', [WebController::class, 'showRegister'])->name('register');
Route::post('/register', [WebController::class, 'register'])->name('register.store');
Route::post('/locale/{locale}', [WebController::class, 'updateLocale'])->name('locale.update');

Route::middleware('auth')->group(function (): void {
    Route::get('/private-area', [DashboardController::class, 'privateArea'])->name('private-area.index');
    Route::patch('/private-area/profile', [DashboardController::class, 'profile'])->name('private-area.profile.update');
    Route::post('/reservations/{reservation}/cancel', [WebController::class, 'cancelReservation'])->name('reservations.cancel');
    Route::post('/logout', [WebController::class, 'logout'])->name('logout');
});

Route::post('/booking-request', [WebController::class, 'bookingRequest'])->name('booking-request.store');
Route::get('/calendar/{apartment}/reservations.ics', [ReservationCalendarController::class, 'apartmentReservations'])
    ->name('calendar.apartment-reservations.ics');

Route::post('/payments/stripe/checkout/{reservation}', [StripeController::class, 'checkout'])->name('payments.stripe.checkout');
Route::get('/payments/stripe/success', [StripeController::class, 'success'])->name('payments.stripe.success');
Route::get('/payments/stripe/cancel', [StripeController::class, 'cancel'])->name('payments.stripe.cancel');
Route::post('/payments/stripe/webhook', [StripeController::class, 'webhook'])->name('payments.stripe.webhook')
    ->withoutMiddleware([VerifyCsrfToken::class]);
Route::post('/stripe/webhook', [StripeController::class, 'webhook'])
    ->name('stripe.webhook.legacy')
    ->withoutMiddleware([VerifyCsrfToken::class]);

Route::match(['GET', 'POST'], '/internal/cron/sync-ics', [InternalCronController::class, 'syncIcs'])
    ->name('internal.cron.sync-ics')
    ->withoutMiddleware([VerifyCsrfToken::class]);

Route::prefix('commands')->group(function() {
    Route::get('/run-manual', function (Request $request) {
        $user = $request->user();
        abort_unless($user, 403);

        Artisan::call('schedule:run', [
            '--no-interaction' => true,
        ]);
        $scheduleOutput = trim((string) Artisan::output());

        Artisan::call('queue:work', [
            '--once' => true,
            '--queue' => 'default',
            '--tries' => 3,
            '--no-interaction' => true,
        ]);
        $queueOutput = trim((string) Artisan::output());

        $name = trim((string) ($user->full_name ?? ''));
        if ($name === '') {
            $name = (string) ($user->email ?? 'Utente sconosciuto');
        }

        Log::info(sprintf(
            '%s ha avviato manualmente i Commands in data %s',
            $name,
            now()->format('d/m H:s')
        ), [
            'user_id' => $user->id,
            'email' => $user->email,
            'path' => $request->path(),
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Comandi avviati manualmente.',
            'schedule_output' => $scheduleOutput,
            'queue_output' => $queueOutput,
        ]);
    })->middleware('auth')->name('commands.run-manual');

    Route::get('/clear-cache', function() {
        Artisan::call('cache:clear');
        return 'Cache cleared';
    });

    Route::get('/optimize', function() {
        Artisan::call('optimize');
        return 'Application optimized';
    });

    Route::get('/update', function() {
        Artisan::call('migrate', ['--force' => true]);
        return 'Application updated';
    });

    Route::get('/phpinfo', function() {
        phpinfo();
    });
});
