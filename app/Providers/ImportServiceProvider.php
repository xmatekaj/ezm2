<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Import\DecimalSeparatorConverter;
use App\Services\Import\ImportManager;

class ImportServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register the decimal separator converter as a singleton
        $this->app->singleton(DecimalSeparatorConverter::class);
        
        // Register the import manager with the converter dependency
        $this->app->singleton(ImportManager::class, function ($app) {
            return new ImportManager($app->make(DecimalSeparatorConverter::class));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Any bootstrapping logic if needed
    }
}