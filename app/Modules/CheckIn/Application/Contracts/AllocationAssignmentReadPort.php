<?php

declare(strict_types=1);

namespace App\Modules\CheckIn\Application\Contracts;

interface AllocationAssignmentReadPort
{
    public function hasActiveAllocation(string $allocationId): bool;

    public function isAllocationActiveForBed(string $allocationId, string $bedId): bool;
}
