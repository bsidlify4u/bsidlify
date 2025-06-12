<?php

namespace App\Foundation;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\PackageManifest as BasePackageManifest;

class PackageManifest extends BasePackageManifest
{
    /**
     * Build the manifest data from the composer.lock file.
     *
     * @return void
     */
    public function build()
    {
        if ($this->files->exists($this->manifestPath)) {
            $this->files->delete($this->manifestPath);
        }

        if (! $this->files->exists($path = $this->vendorPath.'/composer/installed.json')) {
            $this->write([]);
            return;
        }

        $packages = json_decode($this->files->get($path), true);

        $packages = $packages['packages'] ?? $packages;

        $this->write($this->formatPackages($packages));
    }

    /**
     * Format the given packages in the required format.
     *
     * @param  array  $packages
     * @return array
     */
    protected function formatPackages(array $packages)
    {
        return collect($packages)->filter(function ($package) {
            return $this->hasPackageOrBsidlifyKey($package);
        })->mapWithKeys(function ($package) {
            return [$this->format($package['name']) => $this->formatPackage($package)];
        })->all();
    }

    /**
     * Determine if the given package has a package or bsidlify key.
     *
     * @param  array  $package
     * @return bool
     */
    protected function hasPackageOrBsidlifyKey(array $package)
    {
        // Check for both Laravel and Bsidlify keys in the package
        return isset($package['extra']['laravel']) || isset($package['extra']['bsidlify']);
    }

    /**
     * Format the given package.
     *
     * @param  array  $package
     * @return array
     */
    protected function formatPackage(array $package)
    {
        // Prefer bsidlify key over laravel key if both exist
        $extra = isset($package['extra']['bsidlify']) 
                ? $package['extra']['bsidlify'] 
                : ($package['extra']['laravel'] ?? []);

        $formatted = [];

        if (isset($extra['providers'])) {
            $formatted['providers'] = $extra['providers'];
        }

        if (isset($extra['aliases'])) {
            $formatted['aliases'] = $extra['aliases'];
        }

        return $formatted;
    }
} 