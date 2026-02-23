<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InternalCronController;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\WebController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('home');
Route::get('/robots.txt', [DashboardController::class, 'robots'])->name('seo.robots');
Route::get('/sitemap.xml', [DashboardController::class, 'sitemap'])->name('seo.sitemap');

Route::get('/login', [WebController::class, 'showLogin'])->name('login');
Route::post('/login', [WebController::class, 'login'])->name('login.store');
Route::get('/register', [WebController::class, 'showRegister'])->name('register');
Route::post('/register', [WebController::class, 'register'])->name('register.store');

Route::middleware('auth')->group(function (): void {
    Route::get('/private-area', [DashboardController::class, 'privateArea'])->name('private-area.index');
    Route::patch('/private-area/profile', [DashboardController::class, 'profile'])->name('private-area.profile.update');
    Route::post('/reservations/{reservation}/cancel', [WebController::class, 'cancelReservation'])->name('reservations.cancel');
    Route::post('/logout', [WebController::class, 'logout'])->name('logout');
});

Route::post('/booking-request', [WebController::class, 'bookingRequest'])->name('booking-request.store');

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
});
