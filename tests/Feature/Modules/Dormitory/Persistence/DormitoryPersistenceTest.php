<?php

declare(strict_types=1);

use App\Modules\Dormitory\Domain\Enums\PhysicalOccupancyState;
use App\Modules\Dormitory\Domain\Enums\ResourceStatus;
use App\Modules\Dormitory\Infrastructure\Persistence\Models\BedModel;
use App\Modules\Dormitory\Infrastructure\Persistence\Models\BuildingModel;
use App\Modules\Dormitory\Infrastructure\Persistence\Models\DormitoryModel;
use App\Modules\Dormitory\Infrastructure\Persistence\Models\FloorModel;
use App\Modules\Dormitory\Infrastructure\Persistence\Models\RoomModel;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Ramsey\Uuid\Uuid;

function createPersistedDormitory(string $code = 'DORM-P1'): DormitoryModel
{
    return DormitoryModel::query()->create([
        'code' => $code,
        'name' => 'Persistence Dormitory '.$code,
        'status' => ResourceStatus::Available,
    ]);
}

function createPersistedBuilding(DormitoryModel $dormitory, string $code = 'A'): BuildingModel
{
    return BuildingModel::query()->create([
        'dormitory_id' => $dormitory->getId(),
        'code' => $code,
        'name' => 'Building '.$code,
        'status' => ResourceStatus::Available,
    ]);
}

function createPersistedFloor(BuildingModel $building, string $label = '1'): FloorModel
{
    return FloorModel::query()->create([
        'building_id' => $building->getId(),
        'label' => $label,
        'status' => ResourceStatus::Available,
    ]);
}

function createPersistedRoom(FloorModel $floor, string $code = 'R-01', int $capacityTotal = 2): RoomModel
{
    return RoomModel::query()->create([
        'floor_id' => $floor->getId(),
        'code' => $code,
        'name' => 'Room '.$code,
        'capacity_total' => $capacityTotal,
        'status' => ResourceStatus::Available,
    ]);
}

function createPersistedBed(RoomModel $room, string $label = 'B1'): BedModel
{
    return BedModel::query()->create([
        'room_id' => $room->getId(),
        'label' => $label,
        'status' => ResourceStatus::Available,
        'physical_occupancy_state' => PhysicalOccupancyState::Vacant,
    ]);
}

it('creates the dormitory hierarchy tables via migrations', function (): void {
    expect(Schema::hasTable('dormitories'))->toBeTrue()
        ->and(Schema::hasTable('dormitory_buildings'))->toBeTrue()
        ->and(Schema::hasTable('dormitory_floors'))->toBeTrue()
        ->and(Schema::hasTable('dormitory_rooms'))->toBeTrue()
        ->and(Schema::hasTable('dormitory_beds'))->toBeTrue();
});

it('persists a dormitory', function (): void {
    $dormitory = createPersistedDormitory('DORM-SAVE');

    expect($dormitory->getId())->not->toBeEmpty()
        ->and($dormitory->code)->toBe('DORM-SAVE')
        ->and($dormitory->status)->toBe(ResourceStatus::Available)
        ->and(DormitoryModel::query()->find($dormitory->getId()))->not->toBeNull();
});

it('requires a valid dormitory foreign key for buildings', function (): void {
    expect(fn () => BuildingModel::query()->create([
        'dormitory_id' => Uuid::uuid7()->toString(),
        'code' => 'X',
        'name' => 'Orphan Building',
        'status' => ResourceStatus::Available,
    ]))->toThrow(QueryException::class);
});

it('requires a valid building foreign key for floors', function (): void {
    expect(fn () => FloorModel::query()->create([
        'building_id' => Uuid::uuid7()->toString(),
        'label' => '9',
        'status' => ResourceStatus::Available,
    ]))->toThrow(QueryException::class);
});

it('requires a valid floor foreign key for rooms', function (): void {
    expect(fn () => RoomModel::query()->create([
        'floor_id' => Uuid::uuid7()->toString(),
        'code' => 'R-X',
        'name' => 'Orphan Room',
        'capacity_total' => 1,
        'status' => ResourceStatus::Available,
    ]))->toThrow(QueryException::class);
});

it('requires a valid room foreign key for beds', function (): void {
    expect(fn () => BedModel::query()->create([
        'room_id' => Uuid::uuid7()->toString(),
        'label' => 'BX',
        'status' => ResourceStatus::Available,
        'physical_occupancy_state' => PhysicalOccupancyState::Vacant,
    ]))->toThrow(QueryException::class);
});

it('enforces scoped uniqueness for buildings floors rooms and beds', function (): void {
    $dormitory = createPersistedDormitory('DORM-UNIQ');
    $building = createPersistedBuilding($dormitory, 'A');
    $floor = createPersistedFloor($building, '1');
    $room = createPersistedRoom($floor, 'R-01');
    createPersistedBed($room, 'B1');

    expect(fn () => createPersistedBuilding($dormitory, 'A'))->toThrow(QueryException::class);
    expect(fn () => createPersistedFloor($building, '1'))->toThrow(QueryException::class);
    expect(fn () => createPersistedRoom($floor, 'R-01'))->toThrow(QueryException::class);
    expect(fn () => createPersistedBed($room, 'B1'))->toThrow(QueryException::class);
});

it('rejects negative room capacity_total', function (): void {
    $floor = createPersistedFloor(
        createPersistedBuilding(createPersistedDormitory('DORM-CAP')),
    );

    expect(fn () => RoomModel::query()->create([
        'floor_id' => $floor->getId(),
        'code' => 'R-NEG',
        'name' => 'Negative Capacity',
        'capacity_total' => -1,
        'status' => ResourceStatus::Available,
    ]))->toThrow(QueryException::class);
});

it('stores approved resource status and occupancy enum values', function (): void {
    $room = createPersistedRoom(
        createPersistedFloor(createPersistedBuilding(createPersistedDormitory('DORM-ENUM'))),
        'R-ENUM',
        1,
    );
    $room->status = ResourceStatus::Maintenance;
    $room->save();

    $bed = createPersistedBed($room, 'B-ENUM');
    $bed->status = ResourceStatus::Unavailable;
    $bed->physical_occupancy_state = PhysicalOccupancyState::Occupied;
    $bed->save();

    $reloadedRoom = RoomModel::query()->findOrFail($room->getId());
    $reloadedBed = BedModel::query()->findOrFail($bed->getId());

    expect($reloadedRoom->status)->toBe(ResourceStatus::Maintenance)
        ->and($reloadedBed->status)->toBe(ResourceStatus::Unavailable)
        ->and($reloadedBed->physical_occupancy_state)->toBe(PhysicalOccupancyState::Occupied);
});

it('rejects invalid status and occupancy values at the database', function (): void {
    $room = createPersistedRoom(
        createPersistedFloor(createPersistedBuilding(createPersistedDormitory('DORM-CHK'))),
    );

    expect(fn () => DB::table('dormitory_beds')->insert([
        'id' => Uuid::uuid7()->toString(),
        'room_id' => $room->getId(),
        'label' => 'BAD-STATUS',
        'status' => 'reserved',
        'physical_occupancy_state' => PhysicalOccupancyState::Vacant->value,
        'created_at' => now(),
        'updated_at' => now(),
    ]))->toThrow(QueryException::class);

    expect(fn () => DB::table('dormitory_beds')->insert([
        'id' => Uuid::uuid7()->toString(),
        'room_id' => $room->getId(),
        'label' => 'BAD-OCC',
        'status' => ResourceStatus::Available->value,
        'physical_occupancy_state' => 'allocated',
        'created_at' => now(),
        'updated_at' => now(),
    ]))->toThrow(QueryException::class);
});

it('loads hierarchy relationships', function (): void {
    $dormitory = createPersistedDormitory('DORM-REL');
    $building = createPersistedBuilding($dormitory, 'B');
    $floor = createPersistedFloor($building, '2');
    $room = createPersistedRoom($floor, 'R-REL', 3);
    $bed = createPersistedBed($room, 'B-REL');

    $loaded = DormitoryModel::query()
        ->with('buildings.floors.rooms.beds')
        ->findOrFail($dormitory->getId());

    expect($loaded->buildings)->toHaveCount(1)
        ->and($loaded->buildings->first()?->floors)->toHaveCount(1)
        ->and($loaded->buildings->first()?->floors->first()?->rooms)->toHaveCount(1)
        ->and($loaded->buildings->first()?->floors->first()?->rooms->first()?->beds)->toHaveCount(1)
        ->and($bed->room?->floor?->building?->dormitory?->getId())->toBe($dormitory->getId());
});
