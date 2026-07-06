<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Allocation\Domain;

use App\Modules\Allocation\Application\Contracts\AllocationRepositoryContract;
use App\Modules\Allocation\Application\Services\CreateAllocationAction;
use App\Modules\Allocation\Application\Services\ReleaseAllocationAction;
use App\Modules\Allocation\Domain\Exceptions\AllocationOverlapException;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AllocationOverlapTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        bypassAllocationMutationAuthorization();
    }

    #[Test]
    public function it_allows_non_overlapping_assignments_for_the_same_person(): void
    {
        $personId = UuidGenerator::uuid7();
        $bedId = UuidGenerator::uuid7();

        $first = app(CreateAllocationAction::class)->execute(
            personId: $personId,
            bedId: $bedId,
            start: new DateTimeImmutable('2026-07-01', new DateTimeZone('UTC')),
            end: new DateTimeImmutable('2026-07-15', new DateTimeZone('UTC')),
        );

        $second = app(CreateAllocationAction::class)->execute(
            personId: $personId,
            bedId: UuidGenerator::uuid7(),
            start: new DateTimeImmutable('2026-07-16', new DateTimeZone('UTC')),
            end: new DateTimeImmutable('2026-07-31', new DateTimeZone('UTC')),
        );

        $this->assertNotNull($first->id);
        $this->assertNotNull($second->id);
        $this->assertTrue($first->isActive());
        $this->assertTrue($second->isActive());
    }

    #[Test]
    public function it_rejects_overlapping_assignments_for_the_same_person(): void
    {
        $personId = UuidGenerator::uuid7();
        $bedId = UuidGenerator::uuid7();

        app(CreateAllocationAction::class)->execute(
            personId: $personId,
            bedId: $bedId,
            start: new DateTimeImmutable('2026-07-01', new DateTimeZone('UTC')),
            end: new DateTimeImmutable('2026-07-31', new DateTimeZone('UTC')),
        );

        $this->expectException(AllocationOverlapException::class);

        app(CreateAllocationAction::class)->execute(
            personId: $personId,
            bedId: UuidGenerator::uuid7(),
            start: new DateTimeImmutable('2026-07-15', new DateTimeZone('UTC')),
            end: new DateTimeImmutable('2026-08-15', new DateTimeZone('UTC')),
        );
    }

    #[Test]
    public function it_allows_overlap_after_prior_assignment_is_released(): void
    {
        $personId = UuidGenerator::uuid7();
        $bedId = UuidGenerator::uuid7();

        $first = app(CreateAllocationAction::class)->execute(
            personId: $personId,
            bedId: $bedId,
            start: new DateTimeImmutable('2026-07-01', new DateTimeZone('UTC')),
            end: new DateTimeImmutable('2026-07-31', new DateTimeZone('UTC')),
        );

        app(ReleaseAllocationAction::class)->execute(
            allocationId: $first->requireId()->value,
            reason: 'End of assignment',
        );

        $second = app(CreateAllocationAction::class)->execute(
            personId: $personId,
            bedId: UuidGenerator::uuid7(),
            start: new DateTimeImmutable('2026-07-15', new DateTimeZone('UTC')),
            end: new DateTimeImmutable('2026-08-15', new DateTimeZone('UTC')),
        );

        $this->assertTrue($second->isActive());
        $this->assertNotNull(app(AllocationRepositoryContract::class)->findById($second->requireId()));
    }
}
