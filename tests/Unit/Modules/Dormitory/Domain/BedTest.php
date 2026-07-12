<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Dormitory\Domain;

use App\Modules\Dormitory\Domain\Entities\Bed;
use App\Modules\Dormitory\Domain\Enums\PhysicalOccupancyState;
use App\Modules\Dormitory\Domain\Enums\ResourceStatus;
use App\Modules\Dormitory\Domain\Exceptions\InvalidOccupancyTransition;
use App\Modules\Dormitory\Domain\Exceptions\InvalidResourceStateTransition;
use App\Modules\Dormitory\Domain\ValueObjects\BedId;
use App\Modules\Dormitory\Domain\ValueObjects\RoomId;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class BedTest extends TestCase
{
    public function test_available_vacant_bed_can_start_occupancy(): void
    {
        $bed = $this->makeBed(ResourceStatus::Available, PhysicalOccupancyState::Vacant);

        $bed->startOccupancy();

        $this->assertTrue($bed->occupancy->isOccupied());
    }

    public function test_unavailable_bed_cannot_start_occupancy(): void
    {
        $bed = $this->makeBed(ResourceStatus::Unavailable, PhysicalOccupancyState::Vacant);

        $this->expectException(InvalidOccupancyTransition::class);
        $bed->startOccupancy();
    }

    public function test_maintenance_bed_cannot_start_occupancy(): void
    {
        $bed = $this->makeBed(ResourceStatus::Maintenance, PhysicalOccupancyState::Vacant);

        $this->expectException(InvalidOccupancyTransition::class);
        $bed->startOccupancy();
    }

    public function test_inactive_bed_cannot_start_occupancy(): void
    {
        $bed = $this->makeBed(ResourceStatus::Inactive, PhysicalOccupancyState::Vacant);

        $this->expectException(InvalidOccupancyTransition::class);
        $bed->startOccupancy();
    }

    public function test_occupied_bed_cannot_start_occupancy_again(): void
    {
        $bed = $this->makeBed(ResourceStatus::Available, PhysicalOccupancyState::Vacant);
        $bed->startOccupancy();

        $this->expectException(InvalidOccupancyTransition::class);
        $bed->startOccupancy();
    }

    public function test_occupied_bed_can_end_occupancy(): void
    {
        $bed = $this->makeBed(ResourceStatus::Available, PhysicalOccupancyState::Vacant);
        $bed->startOccupancy();

        $bed->endOccupancy();

        $this->assertTrue($bed->occupancy->isVacant());
    }

    public function test_vacant_bed_cannot_end_occupancy(): void
    {
        $bed = $this->makeBed(ResourceStatus::Available, PhysicalOccupancyState::Vacant);

        $this->expectException(InvalidOccupancyTransition::class);
        $bed->endOccupancy();
    }

    public function test_occupied_bed_cannot_be_marked_available_without_ending_occupancy(): void
    {
        $bed = $this->makeBed(ResourceStatus::Available, PhysicalOccupancyState::Vacant);
        $bed->startOccupancy();
        $bed->changeStatus(ResourceStatus::Unavailable);

        $this->expectException(InvalidResourceStateTransition::class);
        $bed->changeStatus(ResourceStatus::Available);
    }

    public function test_assignable_requires_available_and_vacant(): void
    {
        $assignable = $this->makeBed(ResourceStatus::Available, PhysicalOccupancyState::Vacant);
        $occupied = $this->makeBed(ResourceStatus::Available, PhysicalOccupancyState::Vacant);
        $occupied->startOccupancy();
        $reserved = $this->makeBed(ResourceStatus::Available, PhysicalOccupancyState::Reserved);
        $maintenance = $this->makeBed(ResourceStatus::Maintenance, PhysicalOccupancyState::Vacant);

        $this->assertTrue($assignable->isAssignable());
        $this->assertFalse($occupied->isAssignable());
        $this->assertFalse($reserved->isAssignable());
        $this->assertFalse($maintenance->isAssignable());
    }

    public function test_reserve_transitions_vacant_to_reserved(): void
    {
        $bed = $this->makeBed(ResourceStatus::Available, PhysicalOccupancyState::Vacant);

        $bed->reserve();

        $this->assertTrue($bed->occupancy->isReserved());
        $this->assertFalse($bed->isAssignable());
    }

    public function test_occupy_marker_requires_reserved(): void
    {
        $vacant = $this->makeBed(ResourceStatus::Available, PhysicalOccupancyState::Vacant);

        $this->expectException(InvalidOccupancyTransition::class);
        $vacant->applyOccupyMarker();
    }

    public function test_release_inventory_marker_from_reserved(): void
    {
        $bed = $this->makeBed(ResourceStatus::Available, PhysicalOccupancyState::Vacant);
        $bed->reserve();

        $bed->releaseInventoryMarker();

        $this->assertTrue($bed->occupancy->isVacant());
        $this->assertTrue($bed->isAssignable());
    }

    private function makeBed(ResourceStatus $status, PhysicalOccupancyState $occupancy): Bed
    {
        return Bed::create(
            id: BedId::fromString(Uuid::uuid7()->toString()),
            roomId: RoomId::fromString(Uuid::uuid7()->toString()),
            label: 'B1',
            status: $status,
            occupancy: $occupancy,
        );
    }
}
