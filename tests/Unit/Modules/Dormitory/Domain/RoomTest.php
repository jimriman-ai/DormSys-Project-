<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Dormitory\Domain;

use App\Modules\Dormitory\Domain\Entities\Bed;
use App\Modules\Dormitory\Domain\Entities\Room;
use App\Modules\Dormitory\Domain\Enums\PhysicalOccupancyState;
use App\Modules\Dormitory\Domain\Enums\ResourceStatus;
use App\Modules\Dormitory\Domain\Exceptions\InvalidCapacity;
use App\Modules\Dormitory\Domain\Exceptions\InvalidDormitoryHierarchy;
use App\Modules\Dormitory\Domain\ValueObjects\Availability;
use App\Modules\Dormitory\Domain\ValueObjects\BedId;
use App\Modules\Dormitory\Domain\ValueObjects\Capacity;
use App\Modules\Dormitory\Domain\ValueObjects\FloorId;
use App\Modules\Dormitory\Domain\ValueObjects\RoomId;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class RoomTest extends TestCase
{
    public function test_room_accepts_bed_that_belongs_to_it(): void
    {
        $roomId = RoomId::fromString(Uuid::uuid7()->toString());
        $room = $this->makeRoom($roomId, Capacity::of(2));
        $bed = Bed::create(
            id: BedId::fromString(Uuid::uuid7()->toString()),
            roomId: $roomId,
            label: 'B1',
        );

        $room->registerBed($bed);

        $this->assertCount(1, $room->beds());
    }

    public function test_room_rejects_bed_belonging_to_another_room(): void
    {
        $room = $this->makeRoom(RoomId::fromString(Uuid::uuid7()->toString()), Capacity::of(2));
        $bed = Bed::create(
            id: BedId::fromString(Uuid::uuid7()->toString()),
            roomId: RoomId::fromString(Uuid::uuid7()->toString()),
            label: 'B1',
        );

        $this->expectException(InvalidDormitoryHierarchy::class);
        $room->registerBed($bed);
    }

    public function test_room_cannot_exceed_capacity_when_registering_beds(): void
    {
        $roomId = RoomId::fromString(Uuid::uuid7()->toString());
        $room = $this->makeRoom($roomId, Capacity::of(1));
        $room->registerBed(Bed::create(
            id: BedId::fromString(Uuid::uuid7()->toString()),
            roomId: $roomId,
            label: 'B1',
        ));

        $this->expectException(InvalidCapacity::class);
        $room->registerBed(Bed::create(
            id: BedId::fromString(Uuid::uuid7()->toString()),
            roomId: $roomId,
            label: 'B2',
        ));
    }

    public function test_availability_excludes_unavailable_maintenance_inactive_and_occupied_beds(): void
    {
        $roomId = RoomId::fromString(Uuid::uuid7()->toString());
        $room = $this->makeRoom($roomId, Capacity::of(4));

        $room->registerBed(Bed::create(
            id: BedId::fromString(Uuid::uuid7()->toString()),
            roomId: $roomId,
            label: 'available-vacant',
        ));
        $occupied = Bed::create(
            id: BedId::fromString(Uuid::uuid7()->toString()),
            roomId: $roomId,
            label: 'occupied',
        );
        $occupied->startOccupancy();
        $room->registerBed($occupied);
        $room->registerBed(Bed::create(
            id: BedId::fromString(Uuid::uuid7()->toString()),
            roomId: $roomId,
            label: 'maintenance',
            status: ResourceStatus::Maintenance,
        ));
        $room->registerBed(Bed::create(
            id: BedId::fromString(Uuid::uuid7()->toString()),
            roomId: $roomId,
            label: 'inactive',
            status: ResourceStatus::Inactive,
            occupancy: PhysicalOccupancyState::Vacant,
        ));

        $this->assertTrue($room->calculateAvailability()->equals(Availability::of(1)));
    }

    public function test_room_status_blocks_availability_projection(): void
    {
        $roomId = RoomId::fromString(Uuid::uuid7()->toString());
        $room = $this->makeRoom($roomId, Capacity::of(1), ResourceStatus::Maintenance);
        $room->registerBed(Bed::create(
            id: BedId::fromString(Uuid::uuid7()->toString()),
            roomId: $roomId,
            label: 'B1',
        ));

        $this->assertTrue($room->calculateAvailability()->equals(Availability::none()));
    }

    private function makeRoom(
        RoomId $roomId,
        Capacity $capacity,
        ResourceStatus $status = ResourceStatus::Available,
    ): Room {
        return Room::create(
            id: $roomId,
            floorId: FloorId::fromString(Uuid::uuid7()->toString()),
            code: 'R-01',
            name: 'Room 1',
            capacity: $capacity,
            status: $status,
        );
    }
}
