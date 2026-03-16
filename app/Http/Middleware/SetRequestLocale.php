<?php

namespace App\Http\Middleware;

use App\Support\LocalePreference;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetRequestLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->resolveLocale($request);

        app()->setLocale($locale);
        $request->attributes->set('current_locale', $locale);

        return $next($request);
    }

    private function resolveLocale(Request $request): string
    {
        $defaultLocale = LocalePreference::defaultLocale();
        $sessionLocale = LocalePreference::normalizeSupported((string) $request->session()->get('locale'));

        if ($sessionLocale !== null) {
            return $sessionLocale;
        }

        $userLocale = LocalePreference::normalize(
            (string) ($request->user()?->preferred_locale ?? ''),
            $defaultLocale
        );

        if ($request->user()) {
            return $userLocale;
        }

        $browserLocale = $request->getPreferredLanguage(LocalePreference::supportedLocales());

        return LocalePreference::normalize((string) $browserLocale, $defaultLocale);
    }
}
