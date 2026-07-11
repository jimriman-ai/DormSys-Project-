<?php

declare(strict_types=1);

namespace App\Modules\Dormitory\Infrastructure\Repositories;

use App\Modules\Dormitory\Application\Contracts\DormitoryStructureReadRepositoryContract;
use App\Modules\Dormitory\Application\DTOs\BedSummaryData;
use App\Modules\Dormitory\Application\DTOs\BuildingSummaryData;
use App\Modules\Dormitory\Application\DTOs\DormitoryDetailData;
use App\Modules\Dormitory\Application\DTOs\DormitorySummaryData;
use App\Modules\Dormitory\Application\DTOs\FloorSummaryData;
use App\Modules\Dormitory\Application\DTOs\RoomSummaryData;
use App\Modules\Dormitory\Infrastructure\Persistence\Models\BedModel;
use App\Modules\Dormitory\Infrastructure\Persistence\Models\BuildingModel;
use App\Modules\Dormitory\Infrastructure\Persistence\Models\DormitoryModel;
use App\Modules\Dormitory\Infrastructure\Persistence\Models\FloorModel;
use App\Modules\Dormitory\Infrastructure\Persistence\Models\RoomModel;

/**
 * Persistence adapter for dormitory structure reads.
 */
final class DormitoryStructureReadRepository implements DormitoryStructureReadRepositoryContract
{
    /**
     * @return list<DormitorySummaryData>
     */
    public function listDormitories(): array
    {
        return array_values(DormitoryModel::query()
            ->orderBy('code')
            ->get()
            ->map(static fn (DormitoryModel $model): DormitorySummaryData => new DormitorySummaryData(
                id: $model->getId(),
                code: $model->code,
                name: $model->name,
                status: $model->status->value,
            ))
            ->all());
    }

    public function findDormitoryDetail(string $dormitoryId): ?DormitoryDetailData
    {
        $model = DormitoryModel::query()->find($dormitoryId);

        if ($model === null) {
            return null;
        }

        return new DormitoryDetailData(
            id: $model->getId(),
            code: $model->code,
            name: $model->name,
            status: $model->status->value,
        );
    }

    /**
     * @return list<BuildingSummaryData>
     */
    public function listBuildingsByDormitoryId(string $dormitoryId): array
    {
        return array_values(BuildingModel::query()
            ->where('dormitory_id', $dormitoryId)
            ->orderBy('code')
            ->get()
            ->map(static fn (BuildingModel $model): BuildingSummaryData => new BuildingSummaryData(
                id: $model->getId(),
                dormitoryId: $model->dormitory_id,
                code: $model->code,
                name: $model->name,
                status: $model->status->value,
            ))
            ->all());
    }

    /**
     * @return list<FloorSummaryData>
     */
    public function listFloorsByBuildingId(string $buildingId): array
    {
        return array_values(FloorModel::query()
            ->where('building_id', $buildingId)
            ->orderBy('label')
            ->get()
            ->map(static fn (FloorModel $model): FloorSummaryData => new FloorSummaryData(
                id: $model->getId(),
                buildingId: $model->building_id,
                label: $model->label,
                status: $model->status->value,
            ))
            ->all());
    }

    /**
     * @return list<RoomSummaryData>
     */
    public function listRoomsByFloorId(string $floorId): array
    {
        return array_values(RoomModel::query()
            ->where('floor_id', $floorId)
            ->orderBy('code')
            ->get()
            ->map(static fn (RoomModel $model): RoomSummaryData => new RoomSummaryData(
                id: $model->getId(),
                floorId: $model->floor_id,
                code: $model->code,
                name: $model->name,
                capacityTotal: $model->capacity_total,
                status: $model->status->value,
            ))
            ->all());
    }

    /**
     * @return list<BedSummaryData>
     */
    public function listBedsByRoomId(string $roomId): array
    {
        return array_values(BedModel::query()
            ->where('room_id', $roomId)
            ->orderBy('label')
            ->get()
            ->map(static fn (BedModel $model): BedSummaryData => new BedSummaryData(
                id: $model->getId(),
                roomId: $model->room_id,
                label: $model->label,
                status: $model->status->value,
                physicalOccupancyState: $model->physical_occupancy_state->value,
            ))
            ->all());
    }

    public function dormitoryExists(string $dormitoryId): bool
    {
        return DormitoryModel::query()->whereKey($dormitoryId)->exists();
    }

    public function buildingExists(string $buildingId): bool
    {
        return BuildingModel::query()->whereKey($buildingId)->exists();
    }

    public function floorExists(string $floorId): bool
    {
        return FloorModel::query()->whereKey($floorId)->exists();
    }

    public function roomExists(string $roomId): bool
    {
        return RoomModel::query()->whereKey($roomId)->exists();
    }
}
