<?php

declare(strict_types=1);

use App\Modules\Dormitory\Application\Contracts\DormitoryStructureReadContract;
use App\Modules\Dormitory\Domain\Enums\PhysicalOccupancyState;
use App\Modules\Dormitory\Domain\Enums\ResourceStatus;
use App\Modules\Dormitory\Infrastructure\Persistence\Models\BedModel;
use App\Modules\Dormitory\Infrastructure\Persistence\Models\BuildingModel;
use App\Modules\Dormitory\Infrastructure\Persistence\Models\DormitoryModel;
use App\Modules\Dormitory\Infrastructure\Persistence\Models\FloorModel;
use App\Modules\Dormitory\Infrastructure\Persistence\Models\RoomModel;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

function seedHierarchyReadDormitory(string $code = 'HIER-D'): DormitoryModel
{
    return DormitoryModel::query()->create([
        'code' => $code,
        'name' => 'Hierarchy Dormitory',
        'status' => ResourceStatus::Available,
    ]);
}

function seedHierarchyReadBuilding(DormitoryModel $dormitory, string $code = 'A'): BuildingModel
{
    return BuildingModel::query()->create([
        'dormitory_id' => $dormitory->getId(),
        'code' => $code,
        'name' => 'Building '.$code,
        'status' => ResourceStatus::Available,
    ]);
}

function seedHierarchyReadFloor(BuildingModel $building, string $label): FloorModel
{
    return FloorModel::query()->create([
        'building_id' => $building->getId(),
        'label' => $label,
        'status' => ResourceStatus::Available,
    ]);
}

function seedHierarchyReadRoom(FloorModel $floor, string $code, int $capacityTotal = 2): RoomModel
{
    return RoomModel::query()->create([
        'floor_id' => $floor->getId(),
        'code' => $code,
        'name' => 'Room '.$code,
        'capacity_total' => $capacityTotal,
        'status' => ResourceStatus::Available,
    ]);
}

function seedHierarchyReadBed(RoomModel $room, string $label): BedModel
{
    return BedModel::query()->create([
        'room_id' => $room->getId(),
        'label' => $label,
        'status' => ResourceStatus::Available,
        'physical_occupancy_state' => PhysicalOccupancyState::Vacant,
    ]);
}

it('lists floors for a building', function (): void {
    $building = seedHierarchyReadBuilding(seedHierarchyReadDormitory());
    seedHierarchyReadFloor($building, '2');
    seedHierarchyReadFloor($building, '1');
    seedHierarchyReadFloor(seedHierarchyReadBuilding(seedHierarchyReadDormitory('HIER-OTHER'), 'Z'), '9');

    $floors = app(DormitoryStructureReadContract::class)
        ->listBuildingFloors($building->getId());

    expect($floors)->toHaveCount(2)
        ->and($floors[0]->label)->toBe('1')
        ->and($floors[1]->label)->toBe('2')
        ->and($floors[0]->buildingId)->toBe($building->getId())
        ->and($floors[0]->status)->toBe(ResourceStatus::Available->value);
});

it('returns an empty floor list for a missing building', function (): void {
    expect(app(DormitoryStructureReadContract::class)
        ->listBuildingFloors(Uuid::uuid7()->toString()))->toBe([]);
});

it('lists rooms for a floor', function (): void {
    $floor = seedHierarchyReadFloor(
        seedHierarchyReadBuilding(seedHierarchyReadDormitory()),
        '1',
    );
    seedHierarchyReadRoom($floor, 'R-02', 4);
    seedHierarchyReadRoom($floor, 'R-01', 2);
    seedHierarchyReadRoom(
        seedHierarchyReadFloor(seedHierarchyReadBuilding(seedHierarchyReadDormitory('HIER-R'), 'B'), '3'),
        'R-99',
    );

    $rooms = app(DormitoryStructureReadContract::class)
        ->listFloorRooms($floor->getId());

    expect($rooms)->toHaveCount(2)
        ->and($rooms[0]->code)->toBe('R-01')
        ->and($rooms[1]->code)->toBe('R-02')
        ->and($rooms[0]->floorId)->toBe($floor->getId())
        ->and($rooms[0]->capacityTotal)->toBe(2)
        ->and($rooms[1]->capacityTotal)->toBe(4)
        ->and($rooms[0]->name)->toBe('Room R-01')
        ->and($rooms[0]->status)->toBe(ResourceStatus::Available->value);
});

it('returns an empty room list for a missing floor', function (): void {
    expect(app(DormitoryStructureReadContract::class)
        ->listFloorRooms(Uuid::uuid7()->toString()))->toBe([]);
});

it('lists beds for a room', function (): void {
    $room = seedHierarchyReadRoom(
        seedHierarchyReadFloor(seedHierarchyReadBuilding(seedHierarchyReadDormitory()), '1'),
        'R-01',
    );
    seedHierarchyReadBed($room, 'B2');
    seedHierarchyReadBed($room, 'B1');
    seedHierarchyReadBed(
        seedHierarchyReadRoom(
            seedHierarchyReadFloor(seedHierarchyReadBuilding(seedHierarchyReadDormitory('HIER-B'), 'C'), '2'),
            'R-02',
        ),
        'BX',
    );

    $beds = app(DormitoryStructureReadContract::class)
        ->listRoomBeds($room->getId());

    expect($beds)->toHaveCount(2)
        ->and($beds[0]->label)->toBe('B1')
        ->and($beds[1]->label)->toBe('B2')
        ->and($beds[0]->roomId)->toBe($room->getId())
        ->and($beds[0]->status)->toBe(ResourceStatus::Available->value)
        ->and($beds[0]->physicalOccupancyState)->toBe(PhysicalOccupancyState::Vacant->value);
});

it('returns an empty bed list for a missing room', function (): void {
    expect(app(DormitoryStructureReadContract::class)
        ->listRoomBeds(Uuid::uuid7()->toString()))->toBe([]);
});

it('does not write when reading floors rooms and beds', function (): void {
    $building = seedHierarchyReadBuilding(seedHierarchyReadDormitory('HIER-RO'));
    $floor = seedHierarchyReadFloor($building, '1');
    $room = seedHierarchyReadRoom($floor, 'R-01');
    seedHierarchyReadBed($room, 'B1');

    $beforeFloors = FloorModel::query()->count();
    $beforeRooms = RoomModel::query()->count();
    $beforeBeds = BedModel::query()->count();
    $queries = 0;

    DB::listen(function ($query) use (&$queries): void {
        if (preg_match('/^\s*(insert|update|delete)\b/i', $query->sql) === 1) {
            $queries++;
        }
    });

    $reads = app(DormitoryStructureReadContract::class);
    $reads->listBuildingFloors($building->getId());
    $reads->listFloorRooms($floor->getId());
    $reads->listRoomBeds($room->getId());

    expect($queries)->toBe(0)
        ->and(FloorModel::query()->count())->toBe($beforeFloors)
        ->and(RoomModel::query()->count())->toBe($beforeRooms)
        ->and(BedModel::query()->count())->toBe($beforeBeds);
});
