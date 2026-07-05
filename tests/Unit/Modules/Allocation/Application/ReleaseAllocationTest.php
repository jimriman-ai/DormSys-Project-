<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Allocation\Application;

use App\Modules\Allocation\Application\Contracts\AllocationRepositoryContract;
use App\Modules\Allocation\Application\Services\CreateAllocationAction;
use App\Modules\Allocation\Application\Services\ReleaseAllocationAction;
use App\Modules\Allocation\Domain\Exceptions\AllocationNotFoundException;
use App\Modules\Allocation\Domain\Exceptions\InvalidAllocationTransitionException;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use App\Support\Exceptions\ValidationException;
use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\MockeryTest;
use Tests\TestCase;

class ReleaseAllocationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_rejects_release_when_allocation_is_not_found(): void
    {
        $this->expectException(AllocationNotFoundException::class);

        app(ReleaseAllocationAction::class)->execute(
            allocationId: UuidGenerator::uuid7(),
            reason: 'No longer needed',
        );
    }

    #[Test]
    public function it_rejects_release_when_allocation_is_already_released(): void
    {
        $personId = UuidGenerator::uuid7();
        $bedId = UuidGenerator::uuid7();

        $allocation = app(CreateAllocationAction::class)->execute(
            personId: $personId,
            bedId: $bedId,
            start: new DateTimeImmutable('2026-09-01', new DateTimeZone('UTC')),
            end: new DateTimeImmutable('2026-09-30', new DateTimeZone('UTC')),
        );

        app(ReleaseAllocationAction::class)->execute(
            allocationId: $allocation->requireId()->value,
            reason: 'First release',
        );

        $this->expectException(InvalidAllocationTransitionException::class);

        app(ReleaseAllocationAction::class)->execute(
            allocationId: $allocation->requireId()->value,
            reason: 'Second release',
        );
    }

    #[Test]
    public function it_rejects_release_when_reason_is_blank_on_active_allocation(): void
    {
        $personId = UuidGenerator::uuid7();
        $bedId = UuidGenerator::uuid7();

        $allocation = app(CreateAllocationAction::class)->execute(
            personId: $personId,
            bedId: $bedId,
            start: new DateTimeImmutable('2026-10-01', new DateTimeZone('UTC')),
            end: new DateTimeImmutable('2026-10-31', new DateTimeZone('UTC')),
        );

        $this->expectException(ValidationException::class);

        $allocation->release('   ', new DateTimeImmutable('now', new DateTimeZone('UTC')));
    }

    #[Test]
    public function it_rejects_blank_reason_before_persisting_release(): void
    {
        $personId = UuidGenerator::uuid7();
        $bedId = UuidGenerator::uuid7();

        $allocation = app(CreateAllocationAction::class)->execute(
            personId: $personId,
            bedId: $bedId,
            start: new DateTimeImmutable('2026-11-01', new DateTimeZone('UTC')),
            end: new DateTimeImmutable('2026-11-30', new DateTimeZone('UTC')),
        );

        $repository = MockeryTest::mock(AllocationRepositoryContract::class);
        MockeryTest::expectOnce($repository, 'findById')
            ->andReturn($allocation);
        $repository->shouldNotReceive('save');

        $this->app->instance(AllocationRepositoryContract::class, $repository);
        $this->app->forgetInstance(ReleaseAllocationAction::class);

        $this->expectException(ValidationException::class);

        app(ReleaseAllocationAction::class)->execute(
            allocationId: $allocation->requireId()->value,
            reason: '   ',
        );
    }
}
