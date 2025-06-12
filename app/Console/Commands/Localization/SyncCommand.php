<?php

namespace App\Console\Commands\Localization;

use Illuminate\Console\Command;

class SyncCommand extends Command
{
    protected $signature = 'localization:sync
                          {--source=file : Source driver to sync from}
                          {--target=database : Target driver to sync to}
                          {--locales=* : Specific locales to sync}
                          {--force : Force sync without confirmation}';

    protected $description = 'Synchronize translations between different storage backends';

    public function handle()
    {
        $source = $this->option('source');
        $target = $this->option('target');
        $locales = $this->option('locales') ?: $this->getAllLocales();
        
        if (!$this->option('force')) {
            if (!$this->confirm("This will sync translations from {$source} to {$target}. Continue?")) {
                return Command::SUCCESS;
            }
        }

        $manager = app('localization');
        $sourceDriver = $manager->driver($source);
        $targetDriver = $manager->driver($target);

        $bar = $this->output->createProgressBar(count($locales));
        $bar->start();

        foreach ($locales as $locale) {
            $translations = $sourceDriver->getTranslations($locale);
            $targetDriver->setTranslations($locale, $translations);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Translations synchronized successfully!');

        return Command::SUCCESS;
    }

    protected function getAllLocales(): array
    {
        return app('localization')->getAvailableLocales();
    }
}
