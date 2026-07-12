<?php

declare(strict_types=1);

namespace App\Modules\Dormitory\Application\Contracts;

use App\Modules\Dormitory\Domain\Enums\PhysicalOccupancyState;

interface AllocationAssignabilityContract
{
    public function bedExists(string $bedId): bool;

    /**
     * True only when bed exists, inventory status allows assignment, and physical state is VACANT.
     */
    public function isBedAssignable(string $bedId): bool;

    public function getPhysicalOccupancyState(string $bedId): ?PhysicalOccupancyState;
}
