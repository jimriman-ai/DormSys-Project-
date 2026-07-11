<?php

declare(strict_types=1);

namespace App\Modules\Allocation\Application\Services;

/**
 * Assignment → Dormitory occupancy-marker policy (ADIC).
 *
 * AllocationAssigned always reserves. Occupy is optional and only applied when
 * this policy requires an Occupied marker. Default is deferred: CheckIn owns
 * the Occupied transition (CD-015).
 */
final class AssignmentOccupancyMarkerPolicy
{
    public function __construct(
        private readonly bool $requireOccupiedMarkerOnAssign = false,
    ) {}

    public function requiresOccupiedMarkerOnAssign(): bool
    {
        return $this->requireOccupiedMarkerOnAssign;
    }
}
