<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Dormitory\Domain\ValueObjects;

use App\Modules\Dormitory\Domain\Enums\PhysicalOccupancyState;
use PHPUnit\Framework\TestCase;

final class PhysicalOccupancyStateTest extends TestCase
{
    public function test_vacant_reserved_and_occupied_are_distinct_inventory_markers(): void
    {
        $this->assertTrue(PhysicalOccupancyState::Vacant->isVacant());
        $this->assertFalse(PhysicalOccupancyState::Vacant->isOccupied());
        $this->assertFalse(PhysicalOccupancyState::Vacant->isReserved());

        $this->assertTrue(PhysicalOccupancyState::Reserved->isReserved());
        $this->assertFalse(PhysicalOccupancyState::Reserved->isVacant());
        $this->assertFalse(PhysicalOccupancyState::Reserved->isOccupied());

        $this->assertTrue(PhysicalOccupancyState::Occupied->isOccupied());
        $this->assertFalse(PhysicalOccupancyState::Occupied->isVacant());
        $this->assertFalse(PhysicalOccupancyState::Occupied->isReserved());
    }

    public function test_allocation_time_markers_include_reserved(): void
    {
        $cases = array_map(
            static fn (PhysicalOccupancyState $state): string => $state->value,
            PhysicalOccupancyState::cases(),
        );

        $this->assertSame(['vacant', 'reserved', 'occupied'], $cases);
        $this->assertContains('reserved', $cases);
    }
}
