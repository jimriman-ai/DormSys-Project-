<?php

declare(strict_types=1);

namespace App\Modules\Dormitory\Infrastructure\Providers;

use App\Modules\Dormitory\Application\Contracts\Ports\AllocationPhysicalStatePort;
use App\Modules\Dormitory\Infrastructure\Adapters\NullAllocationPhysicalStateAdapter;
use Illuminate\Support\ServiceProvider;

class DormitoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AllocationPhysicalStatePort::class, NullAllocationPhysicalStateAdapter::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(database_path('migrations/modules/dormitory'));
    }
}
