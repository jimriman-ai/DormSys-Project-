<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Dormitory\Domain;

use App\Modules\Dormitory\Domain\Entities\Building;
use App\Modules\Dormitory\Domain\Entities\Floor;
use App\Modules\Dormitory\Domain\Exceptions\InvalidDormitoryHierarchy;
use App\Modules\Dormitory\Domain\ValueObjects\BuildingId;
use App\Modules\Dormitory\Domain\ValueObjects\DormitoryId;
use App\Modules\Dormitory\Domain\ValueObjects\FloorId;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class BuildingTest extends TestCase
{
    public function test_building_accepts_floor_that_belongs_to_it(): void
    {
        $buildingId = BuildingId::fromString(Uuid::uuid7()->toString());
        $building = Building::create(
            id: $buildingId,
            dormitoryId: DormitoryId::fromString(Uuid::uuid7()->toString()),
            code: 'A',
            name: 'Building A',
        );
        $floor = Floor::create(
            id: FloorId::fromString(Uuid::uuid7()->toString()),
            buildingId: $buildingId,
            label: '1',
        );

        $building->addFloor($floor);

        $this->assertCount(1, $building->floors());
    }

    public function test_building_rejects_floor_belonging_to_another_building(): void
    {
        $building = Building::create(
            id: BuildingId::fromString(Uuid::uuid7()->toString()),
            dormitoryId: DormitoryId::fromString(Uuid::uuid7()->toString()),
            code: 'A',
            name: 'Building A',
        );
        $floor = Floor::create(
            id: FloorId::fromString(Uuid::uuid7()->toString()),
            buildingId: BuildingId::fromString(Uuid::uuid7()->toString()),
            label: '1',
        );

        $this->expectException(InvalidDormitoryHierarchy::class);
        $building->addFloor($floor);
    }
}
