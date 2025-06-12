<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string get(string $key, array $replace = [], ?string $locale = null)
 * @method static string choice(string $key, array $replace = [], int $number = 0, ?string $locale = null)
 * @method static void set(string $key, string $value, string $locale)
 * @method static bool has(string $key, string $locale)
 * @method static array getAvailableLocales()
 * @method static \Illuminate\Support\Collection getTranslations(string $locale)
 * @method static void setTranslations(string $locale, \Illuminate\Support\Collection $translations)
 * 
 * @see \App\Internationalization\LocalizationManager
 */
class Localization extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'localization';
    }
}
