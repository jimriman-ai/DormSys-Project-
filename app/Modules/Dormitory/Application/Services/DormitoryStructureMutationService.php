<?php

declare(strict_types=1);

namespace App\Modules\Dormitory\Application\Services;

use App\Modules\Dormitory\Application\Contracts\DormitoryStructureMutationContract;
use App\Modules\Dormitory\Application\Contracts\DormitoryStructureWriteRepositoryContract;
use App\Modules\Dormitory\Application\DTOs\BedOccupancyChangedData;
use App\Modules\Dormitory\Application\DTOs\CreateBedData;
use App\Modules\Dormitory\Application\DTOs\CreateBuildingData;
use App\Modules\Dormitory\Application\DTOs\CreateDormitoryData;
use App\Modules\Dormitory\Application\DTOs\CreatedResourceData;
use App\Modules\Dormitory\Application\DTOs\CreateFloorData;
use App\Modules\Dormitory\Application\DTOs\CreateRoomData;
use App\Modules\Dormitory\Application\DTOs\ResourceStatusChangedData;
use App\Modules\Dormitory\Domain\Entities\Bed;
use App\Modules\Dormitory\Domain\Entities\Building;
use App\Modules\Dormitory\Domain\Entities\Dormitory;
use App\Modules\Dormitory\Domain\Entities\Floor;
use App\Modules\Dormitory\Domain\Entities\Room;
use App\Modules\Dormitory\Domain\Enums\PhysicalOccupancyState;
use App\Modules\Dormitory\Domain\Enums\ResourceStatus;
use App\Modules\Dormitory\Domain\Exceptions\InvalidDormitoryHierarchy;
use App\Modules\Dormitory\Domain\ValueObjects\BedId;
use App\Modules\Dormitory\Domain\ValueObjects\BuildingId;
use App\Modules\Dormitory\Domain\ValueObjects\Capacity;
use App\Modules\Dormitory\Domain\ValueObjects\DormitoryId;
use App\Modules\Dormitory\Domain\ValueObjects\FloorId;
use App\Modules\Dormitory\Domain\ValueObjects\RoomId;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

/**
 * Phase 3C read-free mutation service for dormitory structure and physical state.
 */
final class DormitoryStructureMutationService implements DormitoryStructureMutationContract
{
    public function __construct(
        private readonly DormitoryStructureWriteRepositoryContract $writes,
        private readonly DormitoryStructureAuthorizationGate $authorization,
    ) {}

    public function createDormitory(CreateDormitoryData $data): CreatedResourceData
    {
        $this->authorization->assertStructureManage();

        return DB::transaction(function () use ($data): CreatedResourceData {
            $id = Uuid::uuid7()->toString();
            $status = $this->resolveStatus($data->status);

            $dormitory = Dormitory::create(
                id: DormitoryId::fromString($id),
                code: $data->code,
                name: $data->name,
                status: $status,
            );

            $this->writes->createDormitory(
                id: $dormitory->id->value,
                code: $dormitory->code,
                name: $dormitory->name,
                status: $dormitory->status,
            );

            return new CreatedResourceData($dormitory->id->value);
        });
    }

    public function createBuilding(CreateBuildingData $data): CreatedResourceData
    {
        $this->authorization->assertStructureManage();

        return DB::transaction(function () use ($data): CreatedResourceData {
            if (! $this->writes->dormitoryExists($data->dormitoryId)) {
                throw new InvalidDormitoryHierarchy('Dormitory not found.');
            }

            $id = Uuid::uuid7()->toString();
            $status = $this->resolveStatus($data->status);
            $dormitoryId = DormitoryId::fromString($data->dormitoryId);

            $building = Building::create(
                id: BuildingId::fromString($id),
                dormitoryId: $dormitoryId,
                code: $data->code,
                name: $data->name,
                status: $status,
            );

            $dormitory = $this->reconstituteDormitory($data->dormitoryId);
            $dormitory->addBuilding($building);

            $this->writes->createBuilding(
                id: $building->id->value,
                dormitoryId: $building->dormitoryId->value,
                code: $building->code,
                name: $building->name,
                status: $building->status,
            );

            return new CreatedResourceData($building->id->value);
        });
    }

    public function createFloor(CreateFloorData $data): CreatedResourceData
    {
        $this->authorization->assertStructureManage();

        return DB::transaction(function () use ($data): CreatedResourceData {
            if (! $this->writes->buildingExists($data->buildingId)) {
                throw new InvalidDormitoryHierarchy('Building not found.');
            }

            $id = Uuid::uuid7()->toString();
            $status = $this->resolveStatus($data->status);
            $buildingId = BuildingId::fromString($data->buildingId);

            $floor = Floor::create(
                id: FloorId::fromString($id),
                buildingId: $buildingId,
                label: $data->label,
                status: $status,
            );

            // Parent dormitory id is irrelevant for addFloor ownership checks.
            $building = new Building(
                id: $buildingId,
                dormitoryId: DormitoryId::fromString(Uuid::uuid7()->toString()),
                code: 'parent',
                name: 'parent',
            );
            $building->addFloor($floor);

            $this->writes->createFloor(
                id: $floor->id->value,
                buildingId: $floor->buildingId->value,
                label: $floor->label,
                status: $floor->status,
            );

            return new CreatedResourceData($floor->id->value);
        });
    }

    public function createRoom(CreateRoomData $data): CreatedResourceData
    {
        $this->authorization->assertStructureManage();

        return DB::transaction(function () use ($data): CreatedResourceData {
            if (! $this->writes->floorExists($data->floorId)) {
                throw new InvalidDormitoryHierarchy('Floor not found.');
            }

            $id = Uuid::uuid7()->toString();
            $status = $this->resolveStatus($data->status);
            $floorId = FloorId::fromString($data->floorId);

            $room = Room::create(
                id: RoomId::fromString($id),
                floorId: $floorId,
                code: $data->code,
                name: $data->name,
                capacity: Capacity::of($data->capacityTotal),
                status: $status,
            );

            $floor = new Floor(
                id: $floorId,
                buildingId: BuildingId::fromString(Uuid::uuid7()->toString()),
                label: 'parent',
            );
            $floor->addRoom($room);

            $this->writes->createRoom(
                id: $room->id->value,
                floorId: $room->floorId->value,
                code: $room->code,
                name: $room->name,
                capacityTotal: $room->capacity->total,
                status: $room->status,
            );

            return new CreatedResourceData($room->id->value);
        });
    }

    public function createBed(CreateBedData $data): CreatedResourceData
    {
        $this->authorization->assertStructureManage();

        return DB::transaction(function () use ($data): CreatedResourceData {
            $roomRow = $this->writes->findRoom($data->roomId);

            if ($roomRow === null) {
                throw new InvalidDormitoryHierarchy('Room not found.');
            }

            $id = Uuid::uuid7()->toString();
            $status = $this->resolveStatus($data->status);
            $roomId = RoomId::fromString($data->roomId);

            $room = Room::create(
                id: RoomId::fromString($roomRow['id']),
                floorId: FloorId::fromString($roomRow['floor_id']),
                code: $roomRow['code'],
                name: $roomRow['name'],
                capacity: Capacity::of($roomRow['capacity_total']),
                status: $roomRow['status'],
            );

            foreach ($this->writes->listBedsByRoomId($data->roomId) as $existing) {
                $room->registerBed(Bed::create(
                    id: BedId::fromString($existing['id']),
                    roomId: $roomId,
                    label: $existing['label'],
                    status: $existing['status'],
                    occupancy: $existing['occupancy'],
                ));
            }

            $bed = Bed::create(
                id: BedId::fromString($id),
                roomId: $roomId,
                label: $data->label,
                status: $status,
                occupancy: PhysicalOccupancyState::Vacant,
            );
            $room->registerBed($bed);

            $this->writes->createBed(
                id: $bed->id->value,
                roomId: $bed->roomId->value,
                label: $bed->label,
                status: $bed->status,
                occupancy: $bed->occupancy,
            );

            return new CreatedResourceData($bed->id->value);
        });
    }

    public function changeDormitoryStatus(string $dormitoryId, string $status): ResourceStatusChangedData
    {
        $this->authorization->assertStructureManage();

        return DB::transaction(function () use ($dormitoryId, $status): ResourceStatusChangedData {
            $row = $this->writes->findDormitory($dormitoryId);

            if ($row === null) {
                throw new InvalidDormitoryHierarchy('Dormitory not found.');
            }

            $dormitory = Dormitory::create(
                id: DormitoryId::fromString($row['id']),
                code: $row['code'],
                name: $row['name'],
                status: $row['status'],
            );
            $dormitory->changeStatus($this->resolveStatus($status));

            $this->writes->updateDormitoryStatus($dormitory->id->value, $dormitory->status);

            return new ResourceStatusChangedData(
                id: $dormitory->id->value,
                status: $dormitory->status->value,
            );
        });
    }

    public function changeRoomStatus(string $roomId, string $status): ResourceStatusChangedData
    {
        $this->authorization->assertStructureManage();

        return DB::transaction(function () use ($roomId, $status): ResourceStatusChangedData {
            $row = $this->writes->findRoom($roomId);

            if ($row === null) {
                throw new InvalidDormitoryHierarchy('Room not found.');
            }

            $room = Room::create(
                id: RoomId::fromString($row['id']),
                floorId: FloorId::fromString($row['floor_id']),
                code: $row['code'],
                name: $row['name'],
                capacity: Capacity::of($row['capacity_total']),
                status: $row['status'],
            );
            $room->changeStatus($this->resolveStatus($status));

            $this->writes->updateRoomStatus($room->id->value, $room->status);

            return new ResourceStatusChangedData(
                id: $room->id->value,
                status: $room->status->value,
            );
        });
    }

    public function changeBedStatus(string $bedId, string $status): ResourceStatusChangedData
    {
        $this->authorization->assertStructureManage();

        return DB::transaction(function () use ($bedId, $status): ResourceStatusChangedData {
            $row = $this->writes->findBed($bedId);

            if ($row === null) {
                throw new InvalidDormitoryHierarchy('Bed not found.');
            }

            $bed = Bed::create(
                id: BedId::fromString($row['id']),
                roomId: RoomId::fromString($row['room_id']),
                label: $row['label'],
                status: $row['status'],
                occupancy: $row['occupancy'],
            );
            $bed->changeStatus($this->resolveStatus($status));

            $this->writes->updateBedStatus($bed->id->value, $bed->status);

            return new ResourceStatusChangedData(
                id: $bed->id->value,
                status: $bed->status->value,
            );
        });
    }

    public function recordBedOccupancyStart(string $bedId): BedOccupancyChangedData
    {
        $this->authorization->assertUnresolvedActionDenied();
    }

    public function recordBedOccupancyEnd(string $bedId): BedOccupancyChangedData
    {
        $this->authorization->assertUnresolvedActionDenied();
    }

    private function resolveStatus(?string $status): ResourceStatus
    {
        if ($status === null) {
            return ResourceStatus::Available;
        }

        return ResourceStatus::from($status);
    }

    private function reconstituteDormitory(string $dormitoryId): Dormitory
    {
        $row = $this->writes->findDormitory($dormitoryId);

        if ($row === null) {
            throw new InvalidDormitoryHierarchy('Dormitory not found.');
        }

        return Dormitory::create(
            id: DormitoryId::fromString($row['id']),
            code: $row['code'],
            name: $row['name'],
            status: $row['status'],
        );
    }
}
