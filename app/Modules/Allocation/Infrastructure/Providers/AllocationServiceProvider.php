<?php

declare(strict_types=1);

namespace App\Modules\Allocation\Infrastructure\Providers;

use App\Modules\Allocation\Application\Contracts\AllocationRepositoryContract;
use App\Modules\Allocation\Application\Services\CreateAllocationAction;
use App\Modules\Allocation\Application\Services\ReleaseAllocationAction;
use App\Modules\Allocation\Infrastructure\Repositories\AllocationRepository;
use Illuminate\Support\ServiceProvider;

class AllocationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AllocationRepositoryContract::class, AllocationRepository::class);
        $this->app->singleton(CreateAllocationAction::class);
        $this->app->singleton(ReleaseAllocationAction::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(database_path('migrations/modules/allocation'));
    }
}
