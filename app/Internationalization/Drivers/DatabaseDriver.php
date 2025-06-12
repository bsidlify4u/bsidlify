<?php

namespace App\Internationalization\Drivers;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Support\Collection;

class DatabaseDriver implements DriverInterface
{
    protected $db;
    protected $table;
    protected $cache;
    protected $loaded = [];

    public function __construct(ConnectionInterface $db, string $table, Cache $cache)
    {
        $this->db = $db;
        $this->table = $table;
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
        $this->db->table($this->table)->updateOrInsert(
            ['locale' => $locale, 'key' => $key],
            ['value' => $value]
        );

        $this->cache->forget("translations.{$locale}");
        unset($this->loaded[$locale]);
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
        $this->db->transaction(function () use ($locale, $translations) {
            // Clear existing translations for this locale
            $this->db->table($this->table)->where('locale', $locale)->delete();

            // Insert new translations
            $data = $translations->map(function ($value, $key) use ($locale) {
                return [
                    'locale' => $locale,
                    'key' => $key,
                    'value' => $value,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->values()->all();

            foreach (array_chunk($data, 100) as $chunk) {
                $this->db->table($this->table)->insert($chunk);
            }
        });

        $this->cache->forget("translations.{$locale}");
        unset($this->loaded[$locale]);
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

        $translations = $this->db->table($this->table)
            ->where('locale', $locale)
            ->pluck('value', 'key')
            ->collect();

        $this->cache->put($cacheKey, $translations->all(), now()->addHours(24));
        return $this->loaded[$locale] = $translations;
    }
}
