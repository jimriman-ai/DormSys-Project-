<?php

declare(strict_types=1);

use App\Modules\Allocation\Application\Contracts\AllocationReadContract;
use App\Modules\Allocation\Application\Services\CreateAllocationAction;
use App\Modules\Allocation\Domain\Enums\AllocationStatus;
use App\Shared\Infrastructure\Uuid\UuidGenerator;

it('exposes hasActiveAllocation and active assignment queries via AllocationReadContract', function (): void {
    $personId = UuidGenerator::uuid7();
    $bedId = UuidGenerator::uuid7();

    $read = app(AllocationReadContract::class);

    expect($read->hasActiveAllocation($personId))->toBeFalse();
    expect($read->getActiveAllocationsForPerson($personId))->toBe([]);

    $allocation = runAllocationMutation(fn () => app(CreateAllocationAction::class)->execute(
        personId: $personId,
        bedId: $bedId,
        start: new DateTimeImmutable('2026-08-01', new DateTimeZone('UTC')),
        end: new DateTimeImmutable('2026-08-31', new DateTimeZone('UTC')),
    ));

    expect($read->hasActiveAllocation($personId))->toBeTrue();

    $active = $read->getActiveAllocationsForPerson($personId);

    expect($active)->toHaveCount(1);
    expect($active[0]['allocationId'])->toBe($allocation->requireId()->value);
    expect($active[0]['personId'])->toBe($personId);
    expect($active[0]['bedId'])->toBe($bedId);
    expect($active[0]['status'])->toBe(AllocationStatus::Active->value);

    $summary = $read->getAllocationSummary($allocation->requireId()->value);

    expect($summary)->not->toBeNull();
    $summary = $summary ?? throw new RuntimeException('Allocation summary not found');
    expect($summary)->toHaveKey('allocationId');
    expect($summary['allocationId'])->toBe($allocation->requireId()->value);
});
