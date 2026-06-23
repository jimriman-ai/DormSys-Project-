<?php

declare(strict_types=1);

namespace App\Modules\Identity\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;

class IdentityServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Module bindings will be added in later phases.
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(database_path('migrations/modules/identity'));
    }
}
