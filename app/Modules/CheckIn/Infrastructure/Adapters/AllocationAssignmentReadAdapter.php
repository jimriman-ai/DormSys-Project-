<?php

declare(strict_types=1);

namespace App\Modules\CheckIn\Infrastructure\Adapters;

use App\Modules\Allocation\Application\Contracts\AllocationRepositoryContract;
use App\Modules\Allocation\Domain\ValueObjects\AllocationId;
use App\Modules\CheckIn\Application\Contracts\AllocationAssignmentReadPort;

final class AllocationAssignmentReadAdapter implements AllocationAssignmentReadPort
{
    public function __construct(
        private readonly AllocationRepositoryContract $allocations,
    ) {}

    public function hasActiveAllocation(string $allocationId): bool
    {
        $allocation = $this->allocations->findById(AllocationId::fromString($allocationId));

        return $allocation !== null && $allocation->isActive();
    }

    public function isAllocationActiveForBed(string $allocationId, string $bedId): bool
    {
        $allocation = $this->allocations->findById(AllocationId::fromString($allocationId));

        return $allocation !== null
            && $allocation->isActive()
            && $allocation->bedId === $bedId;
    }
}
