<?php

declare(strict_types=1);

namespace App\Modules\Dormitory\Infrastructure\Repositories;

use App\Modules\Dormitory\Application\Contracts\AllocationBedPhysicalStateRepositoryContract;
use App\Modules\Dormitory\Domain\Enums\PhysicalOccupancyState;
use App\Modules\Dormitory\Infrastructure\Persistence\Models\BedModel;

final class AllocationBedPhysicalStateRepository implements AllocationBedPhysicalStateRepositoryContract
{
    public function findBed(string $bedId): ?array
    {
        $model = BedModel::query()->find($bedId);

        if ($model === null) {
            return null;
        }

        return [
            'id' => $model->getId(),
            'room_id' => $model->room_id,
            'label' => $model->label,
            'status' => $model->status,
            'occupancy' => $model->physical_occupancy_state,
            'last_signal_reference_id' => $model->last_signal_reference_id,
        ];
    }

    public function updateOccupancy(
        string $bedId,
        PhysicalOccupancyState $occupancy,
        ?string $lastSignalReferenceId,
    ): void {
        BedModel::query()->whereKey($bedId)->update([
            'physical_occupancy_state' => $occupancy->value,
            'last_signal_reference_id' => $lastSignalReferenceId,
        ]);
    }
}
