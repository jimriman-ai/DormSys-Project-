<?php

declare(strict_types=1);

use App\Modules\Dormitory\Domain\Enums\PhysicalOccupancyState;
use App\Modules\Dormitory\Domain\Enums\ResourceStatus;
use App\Modules\Dormitory\Infrastructure\Persistence\Models\BedModel;
use App\Modules\Dormitory\Infrastructure\Persistence\Models\BuildingModel;
use App\Modules\Dormitory\Infrastructure\Persistence\Models\DormitoryModel;
use App\Modules\Dormitory\Infrastructure\Persistence\Models\FloorModel;
use App\Modules\Dormitory\Infrastructure\Persistence\Models\RoomModel;

/**
 * Persist a VACANT, available bed (full hierarchy) for Allocation live-assignability tests.
 *
 * @param  string|null  $id  Optional fixed bed UUID (e.g. lottery payloads still pass dormitory_id as bedId).
 */
function createAssignableBedForAllocationTests(
    string $label = 'B1',
    PhysicalOccupancyState $occupancy = PhysicalOccupancyState::Vacant,
    ResourceStatus $status = ResourceStatus::Available,
    ?string $id = null,
): string {
    $suffix = substr(str_replace('.', '', uniqid('', true)), -8);

    $dormitory = DormitoryModel::query()->create([
        'code' => 'ALLOC-'.$suffix,
        'name' => 'Allocation Test Dormitory '.$suffix,
        'status' => ResourceStatus::Available,
    ]);

    $building = BuildingModel::query()->create([
        'dormitory_id' => $dormitory->getId(),
        'code' => 'A',
        'name' => 'Building A',
        'status' => ResourceStatus::Available,
    ]);

    $floor = FloorModel::query()->create([
        'building_id' => $building->getId(),
        'label' => '1',
        'status' => ResourceStatus::Available,
    ]);

    $room = RoomModel::query()->create([
        'floor_id' => $floor->getId(),
        'code' => 'R-'.$suffix,
        'name' => 'Room '.$suffix,
        'capacity_total' => 4,
        'status' => ResourceStatus::Available,
    ]);

    $bed = new BedModel([
        'room_id' => $room->getId(),
        'label' => $label,
        'status' => $status,
        'physical_occupancy_state' => $occupancy,
    ]);

    if ($id !== null) {
        $bed->id = $id;
    }

    $bed->save();

    return $bed->getId();
}
