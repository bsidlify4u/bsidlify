<?php

namespace App\Internationalization\Drivers;

use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Support\Collection;

class FileDriver implements DriverInterface
{
    protected $path;
    protected $cache;
    protected $loaded = [];

    public function __construct(string $path, Cache $cache)
    {
        $this->path = $path;
        $this->cache = $cache;
    }

    public function get(string $key, array $replace = [], ?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();
        $translations = $this->loadTranslations($locale);

        $value = $translations->get($key, $key);
        
        foreach ($replace as $placeholder => $replacement) {
            $value = str_replace(":{$placeholder}", $replacement, $value);
        }

        return $value;
    }

    public function set(string $key, string $value, string $locale): void
    {
        $translations = $this->loadTranslations($locale);
        $translations->put($key, $value);

        $this->saveTranslations($translations, $locale);
        $this->cache->forget("translations.{$locale}");
    }

    public function has(string $key, string $locale): bool
    {
        return $this->loadTranslations($locale)->has($key);
    }

    public function getTranslations(string $locale): Collection
    {
        return $this->loadTranslations($locale);
    }

    public function setTranslations(string $locale, Collection $translations): void
    {
        $this->saveTranslations($translations, $locale);
        $this->cache->forget("translations.{$locale}");
    }

    protected function loadTranslations(string $locale): Collection
    {
        if (isset($this->loaded[$locale])) {
            return $this->loaded[$locale];
        }

        $cacheKey = "translations.{$locale}";
        
        if ($cached = $this->cache->get($cacheKey)) {
            return $this->loaded[$locale] = collect($cached);
        }

        $files = glob("{$this->path}/{$locale}/*.php");
        $translations = collect();

        foreach ($files as $file) {
            $group = basename($file, '.php');
            $items = require $file;

            foreach ($items as $key => $value) {
                $translations->put("{$group}.{$key}", $value);
            }
        }

        $this->cache->put($cacheKey, $translations->all(), now()->addHours(24));
        return $this->loaded[$locale] = $translations;
    }

    protected function saveTranslations(Collection $translations, string $locale): void
    {
        if (!is_dir("{$this->path}/{$locale}")) {
            mkdir("{$this->path}/{$locale}", 0755, true);
        }

        $grouped = $translations->groupBy(function ($item, $key) {
            return explode('.', $key)[0];
        });

        foreach ($grouped as $group => $items) {
            $path = "{$this->path}/{$locale}/{$group}.php";
            $content = "<?php\n\nreturn " . var_export($items->all(), true) . ";\n";
            file_put_contents($path, $content);
        }
    }
}
