<?php

declare(strict_types=1);

namespace App\Modules\System;

use Illuminate\Support\ServiceProvider;

/**
 * Minimal System module loader (DG-SETTINGS-01 / WP-DEBT-04).
 * Ownership: settings migration only — no bindings, routes, or models.
 */
class SystemServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(database_path('migrations/modules/system'));
    }
}
