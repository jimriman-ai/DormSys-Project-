<?php

declare(strict_types=1);

namespace App\Modules\Allocation\Infrastructure\Providers;

use App\Modules\Allocation\Application\Contracts\AllocationRepositoryContract;
use App\Modules\Allocation\Application\Contracts\Ports\DormitoryReadPort;
use App\Modules\Allocation\Application\Contracts\Ports\PhysicalStateSignalPort;
use App\Modules\Allocation\Application\Services\CreateAllocationAction;
use App\Modules\Allocation\Application\Services\CreateAllocationFromRequestAction;
use App\Modules\Allocation\Application\Services\ProposedAllocationConsumer;
use App\Modules\Allocation\Application\Services\ReleaseAllocationAction;
use App\Modules\Allocation\Infrastructure\Adapters\AllocationPhysicalStateAdapter;
use App\Modules\Allocation\Infrastructure\Adapters\DormitoryReadAdapter;
use App\Modules\Allocation\Infrastructure\Adapters\LotteryResultReadAdapter;
use App\Modules\Allocation\Infrastructure\Adapters\NullDormitoryReadAdapter;
use App\Modules\Allocation\Infrastructure\Adapters\NullPhysicalStateSignalAdapter;
use App\Modules\Allocation\Infrastructure\Adapters\RequestReadAdapter;
use App\Modules\Allocation\Infrastructure\Repositories\AllocationRepository;
use App\Modules\Lottery\Application\Contracts\ProposedAllocationPort;
use Illuminate\Support\ServiceProvider;

class AllocationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AllocationRepositoryContract::class, AllocationRepository::class);
        $this->app->singleton(CreateAllocationAction::class);
        $this->app->singleton(ReleaseAllocationAction::class);
        $this->app->singleton(RequestReadAdapter::class);
        $this->app->singleton(LotteryResultReadAdapter::class);
        $this->app->singleton(ProposedAllocationConsumer::class);
        $this->app->singleton(CreateAllocationFromRequestAction::class);
        $this->app->singleton(DormitoryReadPort::class, NullDormitoryReadAdapter::class);
        $this->app->singleton(PhysicalStateSignalPort::class, NullPhysicalStateSignalAdapter::class);
        $this->app->singleton(DormitoryReadAdapter::class);
        $this->app->singleton(AllocationPhysicalStateAdapter::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(database_path('migrations/modules/allocation'));

        $this->app->booted(function (): void {
            $this->app->singleton(ProposedAllocationPort::class, ProposedAllocationConsumer::class);
        });
    }
}
