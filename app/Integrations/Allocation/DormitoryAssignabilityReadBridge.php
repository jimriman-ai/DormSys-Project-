<?php

declare(strict_types=1);

namespace App\Integrations\Allocation;

use App\Modules\Allocation\Application\Contracts\Ports\DormitoryReadPort;
use App\Modules\Dormitory\Application\Contracts\AllocationAssignabilityContract;

/**
 * Allocation → Spec04 Dormitory assignability live bridge.
 */
final class DormitoryAssignabilityReadBridge implements DormitoryReadPort
{
    public function __construct(
        private readonly AllocationAssignabilityContract $assignability,
    ) {}

    public function bedExists(string $bedId): bool
    {
        return $this->assignability->bedExists($bedId);
    }

    public function isBedAssignable(string $bedId): bool
    {
        return $this->assignability->isBedAssignable($bedId);
    }
}
