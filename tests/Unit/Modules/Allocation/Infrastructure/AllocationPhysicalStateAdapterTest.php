<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Allocation\Infrastructure;

use App\Modules\Allocation\Application\Contracts\Ports\PhysicalStateSignalPort;
use App\Modules\Allocation\Infrastructure\Adapters\AllocationPhysicalStateAdapter;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\MockeryTest;
use Tests\TestCase;

class AllocationPhysicalStateAdapterTest extends TestCase
{
    #[Test]
    public function it_signals_assign_via_reserve_and_occupy_port_methods(): void
    {
        $bedId = UuidGenerator::uuid7();
        $signalReferenceId = UuidGenerator::uuid7();

        $port = MockeryTest::mock(PhysicalStateSignalPort::class);
        MockeryTest::expectOnce($port, 'reserveBed')->with($bedId, $signalReferenceId);
        MockeryTest::expectOnce($port, 'occupyBed')->with($bedId, $signalReferenceId);
        $port->shouldNotReceive('releaseBed');

        $adapter = new AllocationPhysicalStateAdapter($port);
        $adapter->signalAssigned($bedId, $signalReferenceId);
    }

    #[Test]
    public function it_signals_release_via_release_bed_port_method(): void
    {
        $bedId = UuidGenerator::uuid7();
        $signalReferenceId = UuidGenerator::uuid7();

        $port = MockeryTest::mock(PhysicalStateSignalPort::class);
        MockeryTest::expectOnce($port, 'releaseBed')->with($bedId, $signalReferenceId);
        $port->shouldNotReceive('reserveBed');
        $port->shouldNotReceive('occupyBed');

        $adapter = new AllocationPhysicalStateAdapter($port);
        $adapter->signalReleased($bedId, $signalReferenceId);
    }
}
