<?php

declare(strict_types=1);

namespace App\Modules\Allocation\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;

class AllocationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Module bindings will be added in later phases.
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(database_path('migrations/modules/allocation'));
    }
}
