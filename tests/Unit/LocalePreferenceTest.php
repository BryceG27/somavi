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
