<?php

declare(strict_types=1);

use App\Modules\Dormitory\Application\Contracts\DormitoryStructureMutationContract;
use App\Modules\Dormitory\Application\DTOs\CreateBedData;
use App\Modules\Dormitory\Application\DTOs\CreateBuildingData;
use App\Modules\Dormitory\Application\DTOs\CreateDormitoryData;
use App\Modules\Dormitory\Application\DTOs\CreateFloorData;
use App\Modules\Dormitory\Application\DTOs\CreateRoomData;
use App\Modules\Dormitory\Domain\Enums\PhysicalOccupancyState;
use App\Modules\Dormitory\Domain\Enums\ResourceStatus;
use App\Modules\Dormitory\Domain\Exceptions\InvalidCapacity;
use App\Modules\Dormitory\Domain\Exceptions\InvalidDormitoryHierarchy;
use App\Modules\Dormitory\Domain\Exceptions\InvalidOccupancyTransition;
use App\Modules\Dormitory\Domain\Exceptions\InvalidResourceStateTransition;
use App\Modules\Dormitory\Infrastructure\Persistence\Models\BedModel;
use App\Modules\Dormitory\Infrastructure\Persistence\Models\BuildingModel;
use App\Modules\Dormitory\Infrastructure\Persistence\Models\DormitoryModel;
use App\Modules\Dormitory\Infrastructure\Persistence\Models\FloorModel;
use App\Modules\Dormitory\Infrastructure\Persistence\Models\RoomModel;
use Ramsey\Uuid\Uuid;

function mutationService(): DormitoryStructureMutationContract
{
    return app(DormitoryStructureMutationContract::class);
}

/**
 * @return array{
 *     dormitoryId: string,
 *     buildingId: string,
 *     floorId: string,
 *     roomId: string
 * }
 */
function seedMutationHierarchy(): array
{
    $mutations = mutationService();

    $dormitory = $mutations->createDormitory(new CreateDormitoryData(
        code: 'MUT-D-'.substr(Uuid::uuid7()->toString(), 0, 8),
        name: 'Mutation Dormitory',
    ));
    $building = $mutations->createBuilding(new CreateBuildingData(
        dormitoryId: $dormitory->id,
        code: 'A',
        name: 'Building A',
    ));
    $floor = $mutations->createFloor(new CreateFloorData(
        buildingId: $building->id,
        label: '1',
    ));
    $room = $mutations->createRoom(new CreateRoomData(
        floorId: $floor->id,
        code: 'R-01',
        name: 'Room R-01',
        capacityTotal: 2,
    ));

    return [
        'dormitoryId' => $dormitory->id,
        'buildingId' => $building->id,
        'floorId' => $floor->id,
        'roomId' => $room->id,
    ];
}

it('creates a dormitory', function (): void {
    $created = mutationService()->createDormitory(new CreateDormitoryData(
        code: 'MUT-CREATE-D',
        name: 'Created Site',
    ));

    $model = DormitoryModel::query()->findOrFail($created->id);

    expect($model->code)->toBe('MUT-CREATE-D')
        ->and($model->name)->toBe('Created Site')
        ->and($model->status)->toBe(ResourceStatus::Available);
});

it('creates building floor room and bed under parents', function (): void {
    $ids = seedMutationHierarchy();
    $bed = mutationService()->createBed(new CreateBedData(
        roomId: $ids['roomId'],
        label: 'B1',
    ));

    /** @var BuildingModel $building */
    $building = BuildingModel::query()->findOrFail($ids['buildingId']);
    /** @var FloorModel $floor */
    $floor = FloorModel::query()->findOrFail($ids['floorId']);
    /** @var RoomModel $room */
    $room = RoomModel::query()->findOrFail($ids['roomId']);
    /** @var BedModel $bedModel */
    $bedModel = BedModel::query()->findOrFail($bed->id);

    expect($building->dormitory_id)->toBe($ids['dormitoryId'])
        ->and($floor->building_id)->toBe($ids['buildingId'])
        ->and($room->floor_id)->toBe($ids['floorId'])
        ->and($bedModel->room_id)->toBe($ids['roomId'])
        ->and($bedModel->physical_occupancy_state)->toBe(PhysicalOccupancyState::Vacant);
});

it('rejects creating a building under a missing dormitory', function (): void {
    expect(fn () => mutationService()->createBuilding(new CreateBuildingData(
        dormitoryId: Uuid::uuid7()->toString(),
        code: 'X',
        name: 'Missing Parent',
    )))->toThrow(InvalidDormitoryHierarchy::class);
});

it('rejects creating a bed beyond room capacity', function (): void {
    $ids = seedMutationHierarchy();
    $mutations = mutationService();

    $mutations->createBed(new CreateBedData(roomId: $ids['roomId'], label: 'B1'));
    $mutations->createBed(new CreateBedData(roomId: $ids['roomId'], label: 'B2'));

    expect(fn () => $mutations->createBed(new CreateBedData(
        roomId: $ids['roomId'],
        label: 'B3',
    )))->toThrow(InvalidCapacity::class);
});

it('changes dormitory and room status', function (): void {
    $ids = seedMutationHierarchy();
    $mutations = mutationService();

    $dormitory = $mutations->changeDormitoryStatus($ids['dormitoryId'], ResourceStatus::Maintenance->value);
    $room = $mutations->changeRoomStatus($ids['roomId'], ResourceStatus::Unavailable->value);

    /** @var DormitoryModel $dormitoryModel */
    $dormitoryModel = DormitoryModel::query()->findOrFail($ids['dormitoryId']);
    /** @var RoomModel $roomModel */
    $roomModel = RoomModel::query()->findOrFail($ids['roomId']);

    expect($dormitory->status)->toBe(ResourceStatus::Maintenance->value)
        ->and($dormitoryModel->status)->toBe(ResourceStatus::Maintenance)
        ->and($room->status)->toBe(ResourceStatus::Unavailable->value)
        ->and($roomModel->status)->toBe(ResourceStatus::Unavailable);
});

it('changes bed status and records occupancy start and end', function (): void {
    $ids = seedMutationHierarchy();
    $mutations = mutationService();
    $bed = $mutations->createBed(new CreateBedData(roomId: $ids['roomId'], label: 'B1'));

    $status = $mutations->changeBedStatus($bed->id, ResourceStatus::Maintenance->value);
    expect($status->status)->toBe(ResourceStatus::Maintenance->value)
        ->and(BedModel::query()->findOrFail($bed->id)->status)->toBe(ResourceStatus::Maintenance);

    $mutations->changeBedStatus($bed->id, ResourceStatus::Available->value);

    $started = $mutations->recordBedOccupancyStart($bed->id);
    expect($started->physicalOccupancyState)->toBe(PhysicalOccupancyState::Occupied->value)
        ->and(BedModel::query()->findOrFail($bed->id)->physical_occupancy_state)->toBe(PhysicalOccupancyState::Occupied);

    $ended = $mutations->recordBedOccupancyEnd($bed->id);
    expect($ended->physicalOccupancyState)->toBe(PhysicalOccupancyState::Vacant->value)
        ->and(BedModel::query()->findOrFail($bed->id)->physical_occupancy_state)->toBe(PhysicalOccupancyState::Vacant);
});

it('rejects marking an occupied bed available', function (): void {
    $ids = seedMutationHierarchy();
    $mutations = mutationService();
    $bed = $mutations->createBed(new CreateBedData(roomId: $ids['roomId'], label: 'B1'));
    $mutations->recordBedOccupancyStart($bed->id);

    expect(fn () => $mutations->changeBedStatus($bed->id, ResourceStatus::Available->value))
        ->toThrow(InvalidResourceStateTransition::class);
});

it('rejects invalid occupancy transitions', function (): void {
    $ids = seedMutationHierarchy();
    $mutations = mutationService();
    $bed = $mutations->createBed(new CreateBedData(roomId: $ids['roomId'], label: 'B1'));

    expect(fn () => $mutations->recordBedOccupancyEnd($bed->id))
        ->toThrow(InvalidOccupancyTransition::class);

    $mutations->changeBedStatus($bed->id, ResourceStatus::Unavailable->value);

    expect(fn () => $mutations->recordBedOccupancyStart($bed->id))
        ->toThrow(InvalidOccupancyTransition::class);
});

it('rejects hierarchy mutations for missing parents', function (): void {
    $missing = Uuid::uuid7()->toString();

    expect(fn () => mutationService()->createFloor(new CreateFloorData(buildingId: $missing, label: '1')))
        ->toThrow(InvalidDormitoryHierarchy::class)
        ->and(fn () => mutationService()->createRoom(new CreateRoomData(
            floorId: $missing,
            code: 'R-01',
            name: 'Room',
            capacityTotal: 1,
        )))->toThrow(InvalidDormitoryHierarchy::class)
        ->and(fn () => mutationService()->createBed(new CreateBedData(roomId: $missing, label: 'B1')))
        ->toThrow(InvalidDormitoryHierarchy::class);
});
