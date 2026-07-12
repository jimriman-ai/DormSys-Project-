<?php

declare(strict_types=1);

namespace App\Modules\Allocation\Infrastructure\Providers;

use App\Modules\Allocation\Application\Contracts\AllocationReadContract;
use App\Modules\Allocation\Application\Contracts\AllocationRepositoryContract;
use App\Modules\Allocation\Application\Contracts\RequestLifecycleCommandPort;
use App\Modules\Allocation\Application\Contracts\VoucherIssuancePort;
use App\Modules\Allocation\Application\Services\AllocationMutationAuthorizationGate;
use App\Modules\Allocation\Application\Services\AllocationReadService;
use App\Modules\Allocation\Application\Services\AssignmentOccupancyMarkerPolicy;
use App\Modules\Allocation\Application\Services\CreateAllocationAction;
use App\Modules\Allocation\Application\Services\CreateAllocationFromRequestAction;
use App\Modules\Allocation\Application\Services\ProposedAllocationConsumer;
use App\Modules\Allocation\Application\Services\ReleaseAllocationAction;
use App\Modules\Allocation\Infrastructure\Adapters\NullVoucherIssuanceAdapter;
use App\Modules\Allocation\Infrastructure\Adapters\RequestLifecycleCommandAdapter;
use App\Modules\Allocation\Infrastructure\Repositories\AllocationRepository;
use Illuminate\Support\ServiceProvider;

class AllocationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AllocationRepositoryContract::class, AllocationRepository::class);
        $this->app->singleton(AllocationMutationAuthorizationGate::class);
        // Default: reserve only; Occupied deferred to CheckIn (CD-015 / ADIC optional occupy).
        $this->app->singleton(AssignmentOccupancyMarkerPolicy::class);
        $this->app->singleton(CreateAllocationAction::class);
        $this->app->singleton(ReleaseAllocationAction::class);
        $this->app->singleton(ProposedAllocationConsumer::class);
        $this->app->singleton(CreateAllocationFromRequestAction::class);
        // DormitoryReadPort / PhysicalStateSignalPort: live bindings in IntegrationServiceProvider.
        $this->app->singleton(AllocationReadService::class);
        $this->app->singleton(AllocationReadContract::class, AllocationReadService::class);
        $this->app->singleton(RequestLifecycleCommandAdapter::class);
        $this->app->singleton(RequestLifecycleCommandPort::class, RequestLifecycleCommandAdapter::class);
        $this->app->singleton(NullVoucherIssuanceAdapter::class);
        $this->app->singleton(VoucherIssuancePort::class, NullVoucherIssuanceAdapter::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(database_path('migrations/modules/allocation'));
    }
}
