<?php

declare(strict_types=1);

namespace App\Modules\Dormitory\Application\Services;

use App\Modules\Dormitory\Application\Contracts\AllocationAssignabilityContract;
use App\Modules\Dormitory\Application\Contracts\AllocationBedPhysicalStateRepositoryContract;
use App\Modules\Dormitory\Domain\Entities\Bed;
use App\Modules\Dormitory\Domain\Enums\PhysicalOccupancyState;
use App\Modules\Dormitory\Domain\ValueObjects\BedId;
use App\Modules\Dormitory\Domain\ValueObjects\RoomId;

final class AllocationAssignabilityService implements AllocationAssignabilityContract
{
    public function __construct(
        private readonly AllocationBedPhysicalStateRepositoryContract $beds,
    ) {}

    public function bedExists(string $bedId): bool
    {
        return $this->beds->findBed($bedId) !== null;
    }

    public function isBedAssignable(string $bedId): bool
    {
        $row = $this->beds->findBed($bedId);

        if ($row === null) {
            return false;
        }

        $bed = Bed::create(
            id: BedId::fromString($row['id']),
            roomId: RoomId::fromString($row['room_id']),
            label: $row['label'],
            status: $row['status'],
            occupancy: $row['occupancy'],
        );

        return $bed->isAssignable();
    }

    public function getPhysicalOccupancyState(string $bedId): ?PhysicalOccupancyState
    {
        $row = $this->beds->findBed($bedId);

        return $row['occupancy'] ?? null;
    }
}
