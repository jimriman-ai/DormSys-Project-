<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Dormitory\Domain;

use App\Modules\Dormitory\Domain\Entities\Floor;
use App\Modules\Dormitory\Domain\Entities\Room;
use App\Modules\Dormitory\Domain\Exceptions\InvalidDormitoryHierarchy;
use App\Modules\Dormitory\Domain\ValueObjects\BuildingId;
use App\Modules\Dormitory\Domain\ValueObjects\Capacity;
use App\Modules\Dormitory\Domain\ValueObjects\FloorId;
use App\Modules\Dormitory\Domain\ValueObjects\RoomId;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class FloorTest extends TestCase
{
    public function test_floor_accepts_room_that_belongs_to_it(): void
    {
        $floorId = FloorId::fromString(Uuid::uuid7()->toString());
        $floor = Floor::create(
            id: $floorId,
            buildingId: BuildingId::fromString(Uuid::uuid7()->toString()),
            label: '1',
        );
        $room = Room::create(
            id: RoomId::fromString(Uuid::uuid7()->toString()),
            floorId: $floorId,
            code: 'R-01',
            name: 'Room 1',
            capacity: Capacity::of(2),
        );

        $floor->addRoom($room);

        $this->assertCount(1, $floor->rooms());
    }

    public function test_floor_rejects_room_belonging_to_another_floor(): void
    {
        $floor = Floor::create(
            id: FloorId::fromString(Uuid::uuid7()->toString()),
            buildingId: BuildingId::fromString(Uuid::uuid7()->toString()),
            label: '1',
        );
        $room = Room::create(
            id: RoomId::fromString(Uuid::uuid7()->toString()),
            floorId: FloorId::fromString(Uuid::uuid7()->toString()),
            code: 'R-01',
            name: 'Room 1',
            capacity: Capacity::of(2),
        );

        $this->expectException(InvalidDormitoryHierarchy::class);
        $floor->addRoom($room);
    }
}
