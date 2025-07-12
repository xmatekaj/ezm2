<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Import\ImportManager;
use App\Services\Import\CsvTemplateGenerator;
use App\Services\Import\ImportMappingService;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register import services as singletons
        $this->app->singleton(ImportManager::class);
        $this->app->singleton(CsvTemplateGenerator::class);
        $this->app->singleton(ImportMappingService::class);
    }

    public function boot(): void
    {
        // Boot logic if needed
    }
}
