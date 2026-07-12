<?php

declare(strict_types=1);

namespace App\Modules\Dormitory\Application\Contracts;

use App\Modules\Dormitory\Domain\Enums\PhysicalOccupancyState;
use App\Modules\Dormitory\Domain\Enums\ResourceStatus;

interface AllocationBedPhysicalStateRepositoryContract
{
    /**
     * @return array{
     *     id: string,
     *     room_id: string,
     *     label: string,
     *     status: ResourceStatus,
     *     occupancy: PhysicalOccupancyState,
     *     last_signal_reference_id: string|null
     * }|null
     */
    public function findBed(string $bedId): ?array;

    public function updateOccupancy(
        string $bedId,
        PhysicalOccupancyState $occupancy,
        ?string $lastSignalReferenceId,
    ): void;
}
