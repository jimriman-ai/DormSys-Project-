<?php

declare(strict_types=1);

namespace App\Modules\Dormitory\Infrastructure\Repositories;

use App\Modules\Dormitory\Application\Contracts\DormitoryStructureWriteRepositoryContract;
use App\Modules\Dormitory\Domain\Enums\PhysicalOccupancyState;
use App\Modules\Dormitory\Domain\Enums\ResourceStatus;
use App\Modules\Dormitory\Infrastructure\Persistence\Models\BedModel;
use App\Modules\Dormitory\Infrastructure\Persistence\Models\BuildingModel;
use App\Modules\Dormitory\Infrastructure\Persistence\Models\DormitoryModel;
use App\Modules\Dormitory\Infrastructure\Persistence\Models\FloorModel;
use App\Modules\Dormitory\Infrastructure\Persistence\Models\RoomModel;

/**
 * Eloquent write adapter for Phase 3C dormitory structure mutations.
 */
final class DormitoryStructureWriteRepository implements DormitoryStructureWriteRepositoryContract
{
    public function createDormitory(string $id, string $code, string $name, ResourceStatus $status): void
    {
        $model = new DormitoryModel;
        $model->id = $id;
        $model->code = $code;
        $model->name = $name;
        $model->status = $status;
        $model->save();
    }

    public function createBuilding(
        string $id,
        string $dormitoryId,
        string $code,
        string $name,
        ResourceStatus $status,
    ): void {
        $model = new BuildingModel;
        $model->id = $id;
        $model->dormitory_id = $dormitoryId;
        $model->code = $code;
        $model->name = $name;
        $model->status = $status;
        $model->save();
    }

    public function createFloor(
        string $id,
        string $buildingId,
        string $label,
        ResourceStatus $status,
    ): void {
        $model = new FloorModel;
        $model->id = $id;
        $model->building_id = $buildingId;
        $model->label = $label;
        $model->status = $status;
        $model->save();
    }

    public function createRoom(
        string $id,
        string $floorId,
        string $code,
        string $name,
        int $capacityTotal,
        ResourceStatus $status,
    ): void {
        $model = new RoomModel;
        $model->id = $id;
        $model->floor_id = $floorId;
        $model->code = $code;
        $model->name = $name;
        $model->capacity_total = $capacityTotal;
        $model->status = $status;
        $model->save();
    }

    public function createBed(
        string $id,
        string $roomId,
        string $label,
        ResourceStatus $status,
        PhysicalOccupancyState $occupancy,
    ): void {
        $model = new BedModel;
        $model->id = $id;
        $model->room_id = $roomId;
        $model->label = $label;
        $model->status = $status;
        $model->physical_occupancy_state = $occupancy;
        $model->save();
    }

    public function updateDormitoryStatus(string $id, ResourceStatus $status): void
    {
        DormitoryModel::query()->whereKey($id)->update([
            'status' => $status->value,
        ]);
    }

    public function updateRoomStatus(string $id, ResourceStatus $status): void
    {
        RoomModel::query()->whereKey($id)->update([
            'status' => $status->value,
        ]);
    }

    public function updateBedStatus(string $id, ResourceStatus $status): void
    {
        BedModel::query()->whereKey($id)->update([
            'status' => $status->value,
        ]);
    }

    public function updateBedOccupancy(string $id, PhysicalOccupancyState $occupancy): void
    {
        BedModel::query()->whereKey($id)->update([
            'physical_occupancy_state' => $occupancy->value,
        ]);
    }

    public function dormitoryExists(string $id): bool
    {
        return DormitoryModel::query()->whereKey($id)->exists();
    }

    public function buildingExists(string $id): bool
    {
        return BuildingModel::query()->whereKey($id)->exists();
    }

    public function floorExists(string $id): bool
    {
        return FloorModel::query()->whereKey($id)->exists();
    }

    public function roomExists(string $id): bool
    {
        return RoomModel::query()->whereKey($id)->exists();
    }

    public function bedExists(string $id): bool
    {
        return BedModel::query()->whereKey($id)->exists();
    }

    public function findDormitory(string $id): ?array
    {
        $model = DormitoryModel::query()->find($id);

        if ($model === null) {
            return null;
        }

        return [
            'id' => $model->getId(),
            'code' => $model->code,
            'name' => $model->name,
            'status' => $model->status,
        ];
    }

    public function findRoom(string $id): ?array
    {
        $model = RoomModel::query()->find($id);

        if ($model === null) {
            return null;
        }

        return [
            'id' => $model->getId(),
            'floor_id' => $model->floor_id,
            'code' => $model->code,
            'name' => $model->name,
            'capacity_total' => $model->capacity_total,
            'status' => $model->status,
        ];
    }

    public function findBed(string $id): ?array
    {
        $model = BedModel::query()->find($id);

        if ($model === null) {
            return null;
        }

        return [
            'id' => $model->getId(),
            'room_id' => $model->room_id,
            'label' => $model->label,
            'status' => $model->status,
            'occupancy' => $model->physical_occupancy_state,
        ];
    }

    /**
     * @return list<array{
     *     id: string,
     *     label: string,
     *     status: ResourceStatus,
     *     occupancy: PhysicalOccupancyState
     * }>
     */
    public function listBedsByRoomId(string $roomId): array
    {
        return array_values(BedModel::query()
            ->where('room_id', $roomId)
            ->orderBy('label')
            ->get()
            ->map(static fn (BedModel $model): array => [
                'id' => $model->getId(),
                'label' => $model->label,
                'status' => $model->status,
                'occupancy' => $model->physical_occupancy_state,
            ])
            ->all());
    }
}
