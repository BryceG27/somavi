<?php

namespace App\Support;

class LocalePreference
{
    /**
     * @var array<string, string>
     */
    private const DEFAULT_LABELS = [
        'it' => 'Italian',
        'en' => 'English',
    ];

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        $configured = (array) config('localization.supported_locales', []);
        $labels = [];

        foreach ($configured as $locale => $meta) {
            $normalizedLocale = is_int($locale)
                ? self::normalizeLocaleCode((string) $meta)
                : self::normalizeLocaleCode((string) $locale);

            if ($normalizedLocale === '') {
                continue;
            }

            $label = '';

            if (is_array($meta)) {
                $label = trim((string) ($meta['label'] ?? ''));
            } elseif (is_string($meta) && ! is_int($locale)) {
                // Backward compatibility with "it" => "Italian" style configs.
                $label = trim($meta);
            }

            $labels[$normalizedLocale] = $label !== ''
                ? $label
                : (self::DEFAULT_LABELS[$normalizedLocale] ?? strtoupper($normalizedLocale));
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

    public static function normalizeSupported(?string $locale): ?string
    {
        $normalized = self::normalizeLocaleCode((string) $locale);

        return in_array($normalized, self::supportedLocales(), true)
            ? $normalized
            : null;
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
