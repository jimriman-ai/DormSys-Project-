<?php

declare(strict_types=1);

namespace App\Integrations\CheckIn;

use App\Modules\Allocation\Application\Contracts\AllocationReadContract;
use App\Modules\CheckIn\Application\Contracts\AllocationAssignmentReadPort;

final class AllocationAssignmentReadBridge implements AllocationAssignmentReadPort
{
    public function __construct(
        private readonly AllocationReadContract $allocations,
    ) {}

    public function hasActiveAllocation(string $allocationId): bool
    {
        $summary = $this->allocations->getAllocationSummary($allocationId);

        return $summary !== null && $summary['status'] === 'active';
    }

    public function isAllocationActiveForBed(string $allocationId, string $bedId): bool
    {
        $summary = $this->allocations->getAllocationSummary($allocationId);

        return $summary !== null
            && $summary['status'] === 'active'
            && $summary['bedId'] === $bedId;
    }
}
