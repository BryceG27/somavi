<?php

namespace App\Support;

class LocalePreference
{
    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        $configured = (array) config('localization.supported_locales', []);
        $labels = [];

        foreach ($configured as $locale => $meta) {
            $normalizedLocale = self::normalizeLocaleCode((string) $locale);

            if ($normalizedLocale === '') {
                continue;
            }

            $label = is_array($meta)
                ? trim((string) ($meta['label'] ?? ''))
                : '';

            $labels[$normalizedLocale] = $label !== ''
                ? $label
                : strtoupper($normalizedLocale);
        }

        return $labels;
    }

    /**
     * @return list<string>
     */
    public static function supportedLocales(): array
    {
        return array_keys(self::labels());
    }

    public static function defaultLocale(): string
    {
        $supported = self::supportedLocales();
        $default = self::normalizeLocaleCode((string) config('localization.default_locale', 'en'));

        if (in_array($default, $supported, true)) {
            return $default;
        }

        return $supported[0] ?? 'en';
    }

    public static function normalize(?string $locale, ?string $fallback = null): string
    {
        $supported = self::supportedLocales();
        $primary = self::normalizeLocaleCode((string) $locale);
        $secondary = self::normalizeLocaleCode((string) $fallback);

        if (in_array($primary, $supported, true)) {
            return $primary;
        }

        if (in_array($secondary, $supported, true)) {
            return $secondary;
        }

        return self::defaultLocale();
    }

    private static function normalizeLocaleCode(string $locale): string
    {
        $normalized = strtolower(trim(str_replace('_', '-', $locale)));

        if ($normalized === '') {
            return '';
        }

        $parts = explode('-', $normalized);

        return trim($parts[0]);
    }
}
