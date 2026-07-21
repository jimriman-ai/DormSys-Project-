<?php

declare(strict_types=1);

namespace App\Modules\System;

use App\Modules\System\Application\Contracts\SettingsReadContract;
use App\Modules\System\Infrastructure\Settings\QueryBuilderSettingsReader;
use Illuminate\Support\ServiceProvider;

/**
 * System module loader (DG-SETTINGS-01 / WP-DEBT-04 / WP-SYS-01).
 * Owns settings migration + SettingsReadContract binding. No Settings Eloquent model.
 */
class SystemServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SettingsReadContract::class, QueryBuilderSettingsReader::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(database_path('migrations/modules/system'));
    }
}
