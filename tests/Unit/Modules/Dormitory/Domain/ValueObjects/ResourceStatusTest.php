<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Dormitory\Domain\ValueObjects;

use App\Modules\Dormitory\Domain\Enums\ResourceStatus;
use PHPUnit\Framework\TestCase;

final class ResourceStatusTest extends TestCase
{
    public function test_only_available_is_usable_and_allows_occupancy(): void
    {
        $this->assertTrue(ResourceStatus::Available->isUsable());
        $this->assertTrue(ResourceStatus::Available->allowsOccupancy());
        $this->assertTrue(ResourceStatus::Available->contributesToAvailability());

        $this->assertFalse(ResourceStatus::Unavailable->allowsOccupancy());
        $this->assertFalse(ResourceStatus::Maintenance->allowsOccupancy());
        $this->assertFalse(ResourceStatus::Inactive->allowsOccupancy());
        $this->assertFalse(ResourceStatus::Unavailable->contributesToAvailability());
        $this->assertFalse(ResourceStatus::Maintenance->contributesToAvailability());
        $this->assertFalse(ResourceStatus::Inactive->contributesToAvailability());
    }
}
