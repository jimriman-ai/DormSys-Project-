<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Dormitory\Domain\ValueObjects;

use App\Modules\Dormitory\Domain\Exceptions\InvalidCapacity;
use App\Modules\Dormitory\Domain\ValueObjects\Capacity;
use PHPUnit\Framework\TestCase;

final class CapacityTest extends TestCase
{
    public function test_capacity_cannot_be_negative(): void
    {
        $this->expectException(InvalidCapacity::class);
        $this->expectExceptionMessage('Capacity cannot be negative.');

        Capacity::of(-1);
    }

    public function test_occupied_capacity_cannot_be_negative(): void
    {
        $this->expectException(InvalidCapacity::class);
        $this->expectExceptionMessage('Occupied capacity cannot be negative.');

        Capacity::of(2, -1);
    }

    public function test_occupied_capacity_cannot_exceed_total(): void
    {
        $this->expectException(InvalidCapacity::class);
        $this->expectExceptionMessage('Occupied capacity cannot exceed total physical capacity.');

        Capacity::of(2, 3);
    }

    public function test_available_is_total_minus_occupied(): void
    {
        $capacity = Capacity::of(4, 1);

        $this->assertSame(3, $capacity->available());
    }
}
