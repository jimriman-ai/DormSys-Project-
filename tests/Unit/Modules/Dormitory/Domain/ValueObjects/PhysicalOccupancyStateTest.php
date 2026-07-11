<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Dormitory\Domain\ValueObjects;

use App\Modules\Dormitory\Domain\Enums\PhysicalOccupancyState;
use PHPUnit\Framework\TestCase;

final class PhysicalOccupancyStateTest extends TestCase
{
    public function test_vacant_and_occupied_are_distinct_physical_states(): void
    {
        $this->assertTrue(PhysicalOccupancyState::Vacant->isVacant());
        $this->assertFalse(PhysicalOccupancyState::Vacant->isOccupied());
        $this->assertTrue(PhysicalOccupancyState::Occupied->isOccupied());
        $this->assertFalse(PhysicalOccupancyState::Occupied->isVacant());
    }

    public function test_assignment_reservation_is_not_modeled_as_physical_occupancy(): void
    {
        $cases = array_map(
            static fn (PhysicalOccupancyState $state): string => $state->value,
            PhysicalOccupancyState::cases(),
        );

        $this->assertSame(['vacant', 'occupied'], $cases);
        $this->assertNotContains('reserved', $cases);
        $this->assertNotContains('allocated', $cases);
    }
}
