<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Dormitory\Domain\ValueObjects;

use App\Modules\Dormitory\Domain\Exceptions\InvalidCapacity;
use App\Modules\Dormitory\Domain\ValueObjects\Availability;
use PHPUnit\Framework\TestCase;

final class AvailabilityTest extends TestCase
{
    public function test_available_count_cannot_be_negative(): void
    {
        $this->expectException(InvalidCapacity::class);
        $this->expectExceptionMessage('Available capacity cannot be negative.');

        new Availability(-1, false);
    }

    public function test_none_is_unavailable(): void
    {
        $availability = Availability::none();

        $this->assertSame(0, $availability->availableCount);
        $this->assertFalse($availability->isAvailable);
    }

    public function test_of_positive_count_is_available(): void
    {
        $availability = Availability::of(2);

        $this->assertSame(2, $availability->availableCount);
        $this->assertTrue($availability->isAvailable);
    }
}
