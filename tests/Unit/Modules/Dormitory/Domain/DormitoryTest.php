<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Dormitory\Domain;

use App\Modules\Dormitory\Domain\Entities\Building;
use App\Modules\Dormitory\Domain\Entities\Dormitory;
use App\Modules\Dormitory\Domain\Exceptions\InvalidDormitoryHierarchy;
use App\Modules\Dormitory\Domain\ValueObjects\BuildingId;
use App\Modules\Dormitory\Domain\ValueObjects\DormitoryId;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use ReflectionClass;

final class DormitoryTest extends TestCase
{
    public function test_dormitory_accepts_building_that_belongs_to_it(): void
    {
        $dormitoryId = DormitoryId::fromString(Uuid::uuid7()->toString());
        $dormitory = Dormitory::create(
            id: $dormitoryId,
            code: 'DORM-1',
            name: 'Main Dormitory',
        );
        $building = Building::create(
            id: BuildingId::fromString(Uuid::uuid7()->toString()),
            dormitoryId: $dormitoryId,
            code: 'A',
            name: 'Building A',
        );

        $dormitory->addBuilding($building);

        $this->assertCount(1, $dormitory->buildings());
    }

    public function test_dormitory_rejects_building_belonging_to_another_dormitory(): void
    {
        $dormitory = Dormitory::create(
            id: DormitoryId::fromString(Uuid::uuid7()->toString()),
            code: 'DORM-1',
            name: 'Main Dormitory',
        );
        $building = Building::create(
            id: BuildingId::fromString(Uuid::uuid7()->toString()),
            dormitoryId: DormitoryId::fromString(Uuid::uuid7()->toString()),
            code: 'A',
            name: 'Building A',
        );

        $this->expectException(InvalidDormitoryHierarchy::class);
        $dormitory->addBuilding($building);
    }

    public function test_dormitory_does_not_model_allocation_assignment_as_occupancy(): void
    {
        $dormitory = Dormitory::create(
            id: DormitoryId::fromString(Uuid::uuid7()->toString()),
            code: 'DORM-1',
            name: 'Main Dormitory',
        );

        $reflection = new ReflectionClass(Dormitory::class);

        $this->assertObjectNotHasProperty('assignment', $dormitory);
        $this->assertObjectNotHasProperty('allocation', $dormitory);
        $this->assertTrue($reflection->hasMethod('addBuilding'));
        $this->assertFalse($reflection->hasMethod('assignEmployee'));
        $this->assertFalse($reflection->hasMethod('allocate'));
    }
}
