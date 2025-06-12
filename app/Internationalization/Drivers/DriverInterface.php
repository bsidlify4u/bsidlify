<?php

namespace App\Internationalization\Drivers;

use Illuminate\Support\Collection;

interface DriverInterface
{
    /**
     * Get a translation
     *
     * @param string $key
     * @param array $replace
     * @param string|null $locale
     * @return string
     */
    public function get(string $key, array $replace = [], ?string $locale = null): string;

    /**
     * Set a translation
     *
     * @param string $key
     * @param string $value
     * @param string $locale
     * @return void
     */
    public function set(string $key, string $value, string $locale): void;

    /**
     * Check if a translation exists
     *
     * @param string $key
     * @param string $locale
     * @return bool
     */
    public function has(string $key, string $locale): bool;

    /**
     * Get all translations for a locale
     *
     * @param string $locale
     * @return \Illuminate\Support\Collection
     */
    public function getTranslations(string $locale): Collection;

    /**
     * Set all translations for a locale
     *
     * @param string $locale
     * @param \Illuminate\Support\Collection $translations
     * @return void
     */
    public function setTranslations(string $locale, Collection $translations): void;
}
