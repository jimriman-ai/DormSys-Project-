<?php

declare(strict_types=1);

use App\Modules\Allocation\Application\Contracts\Ports\DormitoryReadPort;
use App\Modules\Allocation\Application\Contracts\Ports\PhysicalStateSignalPort;
use App\Modules\Allocation\Application\Services\CreateAllocationAction;
use App\Modules\Allocation\Application\Services\ReleaseAllocationAction;
use App\Modules\Allocation\Domain\Events\AllocationAssigned;
use App\Modules\Allocation\Domain\Events\AllocationReleased;
use App\Modules\Allocation\Domain\Exceptions\BedNotAssignableException;
use App\Modules\Allocation\Infrastructure\Adapters\AllocationPhysicalStateAdapter;
use App\Modules\Allocation\Infrastructure\Adapters\DormitoryReadAdapter;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\Support\MockeryTest;

uses(RefreshDatabase::class);

it('emits integration events and physical state signals on assign and release', function (): void {
    Event::fake([AllocationAssigned::class, AllocationReleased::class]);

    $bedId = UuidGenerator::uuid7();
    $personId = UuidGenerator::uuid7();

    $port = MockeryTest::mock(PhysicalStateSignalPort::class);
    MockeryTest::expectOnce($port, 'reserveBed');
    MockeryTest::expectOnce($port, 'occupyBed');
    MockeryTest::expectOnce($port, 'releaseBed');

    app()->instance(PhysicalStateSignalPort::class, $port);
    app()->forgetInstance(AllocationPhysicalStateAdapter::class);
    app()->forgetInstance(CreateAllocationAction::class);
    app()->forgetInstance(ReleaseAllocationAction::class);

    $allocation = runAllocationMutation(fn () => app(CreateAllocationAction::class)->execute(
        personId: $personId,
        bedId: $bedId,
        start: new DateTimeImmutable('2026-08-01', new DateTimeZone('UTC')),
        end: new DateTimeImmutable('2026-08-31', new DateTimeZone('UTC')),
    ));

    Event::assertDispatched(AllocationAssigned::class);

    runAllocationMutation(fn () => app(ReleaseAllocationAction::class)->execute(
        allocationId: $allocation->requireId()->value,
        reason: 'Completed stay',
    ));

    Event::assertDispatched(AllocationReleased::class);
});

it('rejects allocation when bed is not assignable', function (): void {
    $bedId = UuidGenerator::uuid7();
    $personId = UuidGenerator::uuid7();

    $dormitory = MockeryTest::mock(DormitoryReadPort::class);
    MockeryTest::expectOnce($dormitory, 'isBedAssignable')
        ->with($bedId)
        ->andReturn(false);

    app()->instance(DormitoryReadPort::class, $dormitory);
    app()->forgetInstance(DormitoryReadAdapter::class);
    app()->forgetInstance(CreateAllocationAction::class);

    runAllocationMutation(fn () => app(CreateAllocationAction::class)->execute(
        personId: $personId,
        bedId: $bedId,
        start: new DateTimeImmutable('2026-09-01', new DateTimeZone('UTC')),
        end: new DateTimeImmutable('2026-09-30', new DateTimeZone('UTC')),
    ));
})->throws(BedNotAssignableException::class);
