<?php

declare(strict_types=1);

use App\Modules\Allocation\Application\Services\CreateAllocationAction;
use App\Modules\Allocation\Application\Services\ReleaseAllocationAction;
use App\Modules\Allocation\Domain\Enums\AllocationStatus;
use App\Modules\Allocation\Domain\Events\AllocationCreated;
use App\Modules\Allocation\Domain\Events\AllocationReleased;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;

it('assigns and releases an allocation on the happy path', function (): void {
    Event::fake([AllocationCreated::class, AllocationReleased::class]);

    $personId = UuidGenerator::uuid7();
    $bedId = UuidGenerator::uuid7();

    $allocation = app(CreateAllocationAction::class)->execute(
        personId: $personId,
        bedId: $bedId,
        start: new DateTimeImmutable('2026-08-01', new DateTimeZone('UTC')),
        end: new DateTimeImmutable('2026-08-31', new DateTimeZone('UTC')),
    );

    expect($allocation->id)->not->toBeNull();
    expect($allocation->status)->toBe(AllocationStatus::Active);
    expect($allocation->personId->value)->toBe($personId);
    expect($allocation->bedId)->toBe($bedId);
    expect($allocation->items)->not->toBeEmpty();

    Event::assertDispatched(AllocationCreated::class);

    $released = app(ReleaseAllocationAction::class)->execute(
        allocationId: $allocation->requireId()->value,
        reason: 'Assignment completed',
    );

    expect($released->status)->toBe(AllocationStatus::Released);
    expect($released->releaseReason)->toBe('Assignment completed');
    expect($released->releasedAt)->not->toBeNull();

    Event::assertDispatched(AllocationReleased::class);
});

it('runs allocation module migrations', function (): void {
    expect(Schema::hasTable('allocations'))->toBeTrue();
    expect(Schema::hasTable('allocation_items'))->toBeTrue();
});
