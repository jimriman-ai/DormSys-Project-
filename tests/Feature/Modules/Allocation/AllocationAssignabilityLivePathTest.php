<?php

declare(strict_types=1);

use App\Modules\Allocation\Application\Contracts\Ports\DormitoryReadPort;
use App\Modules\Allocation\Application\Services\CreateAllocationAction;
use App\Modules\Allocation\Domain\Exceptions\BedNotAssignableException;
use App\Modules\Allocation\Infrastructure\Adapters\NullDormitoryReadAdapter;
use App\Modules\Dormitory\Application\Contracts\AllocationAssignabilityContract;
use App\Modules\Dormitory\Domain\Enums\PhysicalOccupancyState;
use App\Modules\Dormitory\Infrastructure\Persistence\Models\BedModel;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use Illuminate\Support\Facades\Schema;

it('blocks allocation when Spec04 inventory marker is reserved', function (): void {
    $bedId = createAssignableBedForAllocationTests(occupancy: PhysicalOccupancyState::Reserved);
    $personId = UuidGenerator::uuid7();

    expect(fn () => runAllocationMutation(fn () => app(CreateAllocationAction::class)->execute(
        personId: $personId,
        bedId: $bedId,
        start: new DateTimeImmutable('2026-08-01', new DateTimeZone('UTC')),
        end: new DateTimeImmutable('2026-08-31', new DateTimeZone('UTC')),
    )))->toThrow(BedNotAssignableException::class);

    expect(BedModel::query()->findOrFail($bedId)->physical_occupancy_state)
        ->toBe(PhysicalOccupancyState::Reserved);
});

it('blocks allocation when Spec04 inventory marker is occupied', function (): void {
    $bedId = createAssignableBedForAllocationTests(occupancy: PhysicalOccupancyState::Occupied);
    $personId = UuidGenerator::uuid7();

    expect(fn () => runAllocationMutation(fn () => app(CreateAllocationAction::class)->execute(
        personId: $personId,
        bedId: $bedId,
        start: new DateTimeImmutable('2026-08-01', new DateTimeZone('UTC')),
        end: new DateTimeImmutable('2026-08-31', new DateTimeZone('UTC')),
    )))->toThrow(BedNotAssignableException::class);

    expect(BedModel::query()->findOrFail($bedId)->physical_occupancy_state)
        ->toBe(PhysicalOccupancyState::Occupied);
});

it('allocates against vacant bed and marks inventory reserved', function (): void {
    $bedId = createAssignableBedForAllocationTests();
    $personId = UuidGenerator::uuid7();

    $allocation = runAllocationMutation(fn () => app(CreateAllocationAction::class)->execute(
        personId: $personId,
        bedId: $bedId,
        start: new DateTimeImmutable('2026-08-01', new DateTimeZone('UTC')),
        end: new DateTimeImmutable('2026-08-31', new DateTimeZone('UTC')),
    ));

    $bed = BedModel::query()->findOrFail($bedId);

    expect($allocation->bedId)->toBe($bedId)
        ->and($bed->physical_occupancy_state)->toBe(PhysicalOccupancyState::Reserved)
        ->and($bed->last_signal_reference_id)->toBe($allocation->requireId()->value);
});

it('live assignability provider resolves real Spec04 physical state unlike Null', function (): void {
    expect(Schema::hasColumn('dormitory_beds', 'last_signal_reference_id'))->toBeTrue();

    $reservedBedId = createAssignableBedForAllocationTests(occupancy: PhysicalOccupancyState::Reserved);
    $vacantBedId = createAssignableBedForAllocationTests();

    $live = app(AllocationAssignabilityContract::class);
    $null = new NullDormitoryReadAdapter;

    expect($live->getPhysicalOccupancyState($reservedBedId))->toBe(PhysicalOccupancyState::Reserved)
        ->and($live->isBedAssignable($reservedBedId))->toBeFalse()
        ->and($live->isBedAssignable($vacantBedId))->toBeTrue()
        ->and($null->isBedAssignable($reservedBedId))->toBeTrue()
        ->and(app(DormitoryReadPort::class)->isBedAssignable($reservedBedId))->toBeFalse()
        ->and(app(DormitoryReadPort::class))->not->toBeInstanceOf(NullDormitoryReadAdapter::class);
});
