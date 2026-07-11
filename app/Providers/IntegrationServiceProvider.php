<?php

declare(strict_types=1);

namespace App\Providers;

use App\Integrations\Allocation\ApprovedRequestReadBridge;
use App\Integrations\CheckIn\AllocationAssignmentReadBridge;
use App\Integrations\Request\DormitoryReadBridge;
use App\Integrations\Request\EmployeeEligibilityBridge;
use App\Integrations\Request\PendingRequestReadBridge;
use App\Modules\Allocation\Application\Contracts\Ports\ApprovedRequestReadPort;
use App\Modules\Allocation\Application\Services\ProposedAllocationConsumer;
use App\Modules\CheckIn\Application\Contracts\AllocationAssignmentReadPort;
use App\Modules\Employee\Application\Contracts\Ports\PendingRequestReadPort;
use App\Modules\Lottery\Application\Contracts\ProposedAllocationPort;
use App\Modules\Request\Application\Contracts\DormitoryReadContract;
use App\Modules\Request\Application\Contracts\Internal\RequestEligibilityGatewayContract;
use Illuminate\Support\ServiceProvider;

final class IntegrationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ApprovedRequestReadPort::class, ApprovedRequestReadBridge::class);
        $this->app->singleton(AllocationAssignmentReadPort::class, AllocationAssignmentReadBridge::class);
        $this->app->singleton(RequestEligibilityGatewayContract::class, EmployeeEligibilityBridge::class);
        $this->app->singleton(PendingRequestReadPort::class, PendingRequestReadBridge::class);
        $this->app->singleton(ProposedAllocationPort::class, ProposedAllocationConsumer::class);
        $this->app->singleton(DormitoryReadContract::class, DormitoryReadBridge::class);
    }
}
