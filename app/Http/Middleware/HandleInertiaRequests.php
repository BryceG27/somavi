<?php

namespace App\Http\Middleware;

use App\Support\LocalePreference;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $currentLocale = LocalePreference::normalize(
            (string) $request->attributes->get('current_locale'),
            LocalePreference::defaultLocale()
        );

        return array_merge(parent::share($request), [
            'flash' => [
                'booking_notice' => fn () => $request->session()->get('booking_notice'),
            ],
            'localization' => [
                'supported_locales' => fn () => LocalePreference::supportedLocales(),
                'locale_labels' => fn () => LocalePreference::labels(),
                'default_locale' => fn () => LocalePreference::defaultLocale(),
                'current_locale' => fn () => $currentLocale,
            ],
            'routes' => [
                'home' => route('home'),
                'login' => route('login'),
                'register' => route('register'),
                'logout' => route('logout'),
                'private_area' => route('private-area.index'),
                'private_area_profile_update' => route('private-area.profile.update'),
                'booking_request' => route('booking-request.store'),
                'reservation_cancel_template' => route('reservations.cancel', ['reservation' => '__reservation__']),
                'stripe_checkout_template' => route('payments.stripe.checkout', ['reservation' => '__reservation__']),
                'locale_update_template' => route('locale.update', ['locale' => '__locale__']),
            ],
        ]);
    }
}
