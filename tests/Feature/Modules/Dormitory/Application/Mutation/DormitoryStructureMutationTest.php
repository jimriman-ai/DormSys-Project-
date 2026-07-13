<?php

declare(strict_types=1);

use App\Modules\Dormitory\Application\Contracts\DormitoryStructureMutationContract;
use App\Modules\Dormitory\Application\DTOs\CreateBedData;
use App\Modules\Dormitory\Application\DTOs\CreateBuildingData;
use App\Modules\Dormitory\Application\DTOs\CreateDormitoryData;
use App\Modules\Dormitory\Application\DTOs\CreateFloorData;
use App\Modules\Dormitory\Application\DTOs\CreateRoomData;
use App\Modules\Dormitory\Application\Exceptions\UnauthorizedDormitoryStructureAccessException;
use App\Modules\Dormitory\Domain\Enums\PhysicalOccupancyState;
use App\Modules\Dormitory\Domain\Enums\ResourceStatus;
use App\Modules\Dormitory\Domain\Exceptions\InvalidDormitoryHierarchy;
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
    return withDormitoryStructureManageActor(function (): array {
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
    });
}

it('creates a dormitory', function (): void {
    $created = withDormitoryStructureManageActor(
        fn () => mutationService()->createDormitory(new CreateDormitoryData(
            code: 'MUT-CREATE-D',
            name: 'Created Site',
        )),
    );

    $model = DormitoryModel::query()->findOrFail($created->id);

    expect($model->code)->toBe('MUT-CREATE-D')
        ->and($model->name)->toBe('Created Site')
        ->and($model->status)->toBe(ResourceStatus::Available);
});

it('creates building floor room and bed under parents', function (): void {
    $ids = seedMutationHierarchy();
    $bed = withDormitoryStructureManageActor(
        fn () => mutationService()->createBed(new CreateBedData(
            roomId: $ids['roomId'],
            label: 'B1',
        )),
    );

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
        ->and($bedModel->label)->toBe('B1');
});

it('rejects building create when dormitory is missing', function (): void {
    expect(fn () => withDormitoryStructureManageActor(
        fn () => mutationService()->createBuilding(new CreateBuildingData(
            dormitoryId: Uuid::uuid7()->toString(),
            code: 'X',
            name: 'Missing Parent',
        )),
    ))->toThrow(InvalidDormitoryHierarchy::class);
});

it('changes dormitory status', function (): void {
    $ids = seedMutationHierarchy();
    $changed = withDormitoryStructureManageActor(
        fn () => mutationService()->changeDormitoryStatus(
            $ids['dormitoryId'],
            ResourceStatus::Unavailable->value,
        ),
    );

    expect($changed->status)->toBe(ResourceStatus::Unavailable->value)
        ->and(DormitoryModel::query()->findOrFail($ids['dormitoryId'])->status)
        ->toBe(ResourceStatus::Unavailable);
});

it('changes room status', function (): void {
    $ids = seedMutationHierarchy();
    $mutations = mutationService();

    $changed = withDormitoryStructureManageActor(function () use ($mutations, $ids) {
        return $mutations->changeRoomStatus($ids['roomId'], ResourceStatus::Unavailable->value);
    });

    /** @var RoomModel $room */
    $room = RoomModel::query()->findOrFail($ids['roomId']);

    expect($changed->status)->toBe(ResourceStatus::Unavailable->value)
        ->and($room->status)->toBe(ResourceStatus::Unavailable);
});

it('changes bed status', function (): void {
    $ids = seedMutationHierarchy();
    $bed = withDormitoryStructureManageActor(
        fn () => mutationService()->createBed(new CreateBedData(roomId: $ids['roomId'], label: 'B1')),
    );

    $status = withDormitoryStructureManageActor(
        fn () => mutationService()->changeBedStatus($bed->id, ResourceStatus::Maintenance->value),
    );

    expect($status->status)->toBe(ResourceStatus::Maintenance->value)
        ->and(BedModel::query()->findOrFail($bed->id)->status)->toBe(ResourceStatus::Maintenance);
});

it('denies unresolved occupancy marker APIs by default', function (): void {
    $ids = seedMutationHierarchy();
    $bed = withDormitoryStructureManageActor(
        fn () => mutationService()->createBed(new CreateBedData(roomId: $ids['roomId'], label: 'B1')),
    );

    expect(fn () => withDormitoryStructureManageActor(
        fn () => mutationService()->recordBedOccupancyStart($bed->id),
    ))->toThrow(UnauthorizedDormitoryStructureAccessException::class)
        ->and(fn () => withDormitoryStructureManageActor(
            fn () => mutationService()->recordBedOccupancyEnd($bed->id),
        ))->toThrow(UnauthorizedDormitoryStructureAccessException::class);
});

it('rejects marking an occupied bed available', function (): void {
    $ids = seedMutationHierarchy();
    $bed = withDormitoryStructureManageActor(
        fn () => mutationService()->createBed(new CreateBedData(roomId: $ids['roomId'], label: 'B1')),
    );

    BedModel::query()->whereKey($bed->id)->update([
        'physical_occupancy_state' => PhysicalOccupancyState::Occupied,
    ]);

    expect(fn () => withDormitoryStructureManageActor(
        fn () => mutationService()->changeBedStatus($bed->id, ResourceStatus::Available->value),
    ))->toThrow(InvalidResourceStateTransition::class);
});

it('rejects hierarchy mutations for missing parents', function (): void {
    $missing = Uuid::uuid7()->toString();

    expect(fn () => withDormitoryStructureManageActor(
        fn () => mutationService()->createFloor(new CreateFloorData(buildingId: $missing, label: '1')),
    ))->toThrow(InvalidDormitoryHierarchy::class)
        ->and(fn () => withDormitoryStructureManageActor(
            fn () => mutationService()->createRoom(new CreateRoomData(
                floorId: $missing,
                code: 'R-01',
                name: 'Room',
                capacityTotal: 1,
            )),
        ))->toThrow(InvalidDormitoryHierarchy::class)
        ->and(fn () => withDormitoryStructureManageActor(
            fn () => mutationService()->createBed(new CreateBedData(roomId: $missing, label: 'B1')),
        ))->toThrow(InvalidDormitoryHierarchy::class);
});
