<?php

declare(strict_types=1);

namespace App\Modules\CheckIn\Infrastructure\Providers;

use App\Modules\CheckIn\Application\Contracts\CheckInRecordRepositoryContract;
use App\Modules\CheckIn\Infrastructure\Repositories\CheckInRecordRepository;
use Illuminate\Support\ServiceProvider;

class CheckInServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CheckInRecordRepositoryContract::class, CheckInRecordRepository::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(database_path('migrations/modules/check_in'));
    }
}
