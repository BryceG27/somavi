<?php

use App\Support\LocalePreference;
use Tests\TestCase;

uses(TestCase::class);

it('normalizes locale codes and falls back safely', function () {
    config()->set('localization.supported_locales', [
        'it' => ['label' => 'Italiano'],
        'en' => ['label' => 'English'],
    ]);
    config()->set('localization.default_locale', 'en');

    expect(LocalePreference::normalize('it-IT'))->toBe('it')
        ->and(LocalePreference::normalize('en_US'))->toBe('en')
        ->and(LocalePreference::normalize('fr', 'it'))->toBe('it')
        ->and(LocalePreference::normalize(null, null))->toBe('en');
});

it('returns null for unsupported locales without fallback coercion', function () {
    config()->set('localization.supported_locales', [
        'it' => ['label' => 'Italiano'],
        'en' => ['label' => 'English'],
    ]);

    expect(LocalePreference::normalizeSupported('it-IT'))->toBe('it')
        ->and(LocalePreference::normalizeSupported('fr-FR'))->toBeNull();
});

it('builds locale labels from both legacy and modern config formats', function () {
    config()->set('localization.supported_locales', [
        'it' => 'Italian',
        'en' => ['label' => 'English'],
    ]);

    expect(LocalePreference::labels())->toBe([
        'it' => 'Italian',
        'en' => 'English',
    ]);
});

it('uses friendly fallback labels for known locales', function () {
    config()->set('localization.supported_locales', [
        'it' => [],
        'en' => [],
    ]);

    expect(LocalePreference::labels())->toBe([
        'it' => 'Italian',
        'en' => 'English',
    ]);
});
