<?php

namespace App\Internationalization;

use Illuminate\Support\Manager;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Component\Translation\Formatter\MessageFormatter;

class LocalizationManager extends Manager
{
    protected $detectedLocale;
    protected $fallbackChain = [];
    protected $pluralRules = [];
    protected $formatters = [];
    protected $cache = [];

    /**
     * Get the default driver
     */
    public function getDefaultDriver(): string
    {
        return $this->config->get('app.locale_driver', 'file');
    }

    /**
     * Create the file driver
     */
    protected function createFileDriver()
    {
        return new FileLocalization(
            $this->app['files'],
            $this->app['path.lang'],
            new YamlFileLoader(),
            new MessageFormatter()
        );
    }

    /**
     * Create the database driver
     */
    protected function createDatabaseDriver()
    {
        return new DatabaseLocalization(
            $this->app['db'],
            new MessageFormatter()
        );
    }

    /**
     * Detect user's preferred locale
     */
    public function detectLocale(array $hints = []): string
    {
        if ($this->detectedLocale) {
            return $this->detectedLocale;
        }

        $locale = $this->determineLocale($hints);
        $this->setLocale($locale);

        return $locale;
    }

    /**
     * Set up a fallback chain for translations
     */
    public function setFallbackChain(array $locales): self
    {
        $this->fallbackChain = $locales;
        return $this;
    }

    /**
     * Register custom plural rules for a locale
     */
    public function registerPluralRules(string $locale, callable $rules): self
    {
        $this->pluralRules[$locale] = $rules;
        return $this;
    }

    /**
     * Register a custom message formatter
     */
    public function registerFormatter(string $name, callable $formatter): self
    {
        $this->formatters[$name] = $formatter;
        return $this;
    }

    /**
     * Translate with context and plurality
     */
    public function translateChoice(string $key, int $number, array $replace = [], ?string $locale = null): string
    {
        $locale = $locale ?: $this->getLocale();
        
        // Get plural form using registered rules or default
        $pluralForm = $this->getPluralForm($locale, $number);
        
        $key = $key . '.' . $pluralForm;
        return $this->translate($key, $replace, $locale);
    }

    /**
     * Format a localized message with custom formatting
     */
    public function formatMessage(string $message, array $parameters = [], string $formatter = 'default'): string
    {
        if (isset($this->formatters[$formatter])) {
            return call_user_func($this->formatters[$formatter], $message, $parameters);
        }

        return (new MessageFormatter())->format($message, $parameters);
    }

    /**
     * Get available locales with their metadata
     */
    public function getAvailableLocales(): array
    {
        return $this->driver()->getAvailableLocales();
    }

    /**
     * Export translations to various formats
     */
    public function export(string $locale, string $format = 'json'): string
    {
        $translations = $this->driver()->getTranslations($locale);

        switch ($format) {
            case 'json':
                return json_encode($translations, JSON_PRETTY_PRINT);
            case 'yaml':
                return yaml_emit($translations);
            case 'php':
                return var_export($translations, true);
            default:
                throw new \InvalidArgumentException("Unsupported export format: {$format}");
        }
    }

    /**
     * Import translations from various formats
     */
    public function import(string $locale, string $content, string $format = 'json'): bool
    {
        $translations = match ($format) {
            'json' => json_decode($content, true),
            'yaml' => yaml_parse($content),
            'php' => include($content),
            default => throw new \InvalidArgumentException("Unsupported import format: {$format}"),
        };

        return $this->driver()->setTranslations($locale, $translations);
    }

    protected function determineLocale(array $hints = []): string
    {
        // Check explicit hint
        if (!empty($hints['locale'])) {
            return $hints['locale'];
        }

        // Check session
        if ($locale = session()->get('locale')) {
            return $locale;
        }

        // Check browser preferences
        if ($locale = request()->getPreferredLanguage($this->getAvailableLocales())) {
            return $locale;
        }

        // Fall back to default
        return $this->config->get('app.fallback_locale', 'en');
    }

    protected function getPluralForm(string $locale, int $number): string
    {
        if (isset($this->pluralRules[$locale])) {
            return call_user_func($this->pluralRules[$locale], $number);
        }

        // Default plural rules
        return $number === 1 ? 'one' : 'other';
    }
}
