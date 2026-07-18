<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Request\Application;

use App\Modules\Request\Application\Contracts\RequestRepositoryContract;
use App\Modules\Request\Application\Contracts\Stage1ApproverIdentityReadContract;
use App\Modules\Request\Application\Services\AssignStage1ApproverSnapshotAction;
use App\Modules\Request\Application\Services\CreatePersonalRequestAction;
use App\Modules\Request\Application\Services\RequestCodeGenerator;
use App\Modules\Request\Domain\Entities\Request;
use App\Modules\Request\Domain\Exceptions\Stage1ApproverUnresolvedException;
use App\Modules\Request\Domain\ValueObjects\DormitorySiteId;
use App\Modules\Request\Domain\ValueObjects\EmployeeReferenceId;
use App\Modules\Request\Domain\ValueObjects\RequestId;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use DateTimeImmutable;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * [PERMIT-ID: IMPL-PERMIT-02] Create-time Stage-1 approver snapshot.
 */
final class CreatePersonalRequestStage1SnapshotTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function it_snapshots_stage1_approver_identity_on_create(): void
    {
        $employeeId = UuidGenerator::uuid7();
        $approverIdentityId = UuidGenerator::uuid7();
        $dormitoryId = UuidGenerator::uuid7();

        $orgChart = Mockery::mock(Stage1ApproverIdentityReadContract::class);
        $orgChart->shouldReceive('resolveForEmployee')
            ->once()
            ->with($employeeId)
            ->andReturn($approverIdentityId);

        $codeRepo = Mockery::mock(RequestRepositoryContract::class);
        $codeRepo->shouldReceive('nextDailySequenceForUtcDate')->once()->andReturn(1);
        $codeGenerator = new RequestCodeGenerator($codeRepo);

        $requests = Mockery::mock(RequestRepositoryContract::class);
        $requests->shouldReceive('save')
            ->once()
            ->with(Mockery::on(function (Request $request) use ($approverIdentityId): bool {
                return $request->assignedStage1ApproverIdentityId === $approverIdentityId;
            }))
            ->andReturnUsing(function (Request $request): Request {
                return $request->assignId(RequestId::fromString(UuidGenerator::uuid7()));
            });

        $action = new CreatePersonalRequestAction(
            codeGenerator: $codeGenerator,
            requests: $requests,
            assignStage1Approver: new AssignStage1ApproverSnapshotAction($orgChart),
        );

        $created = $action->execute(
            employeeId: EmployeeReferenceId::fromString($employeeId),
            dormitoryId: DormitorySiteId::fromString($dormitoryId),
            checkInDate: new DateTimeImmutable('2026-07-01'),
            checkOutDate: new DateTimeImmutable('2026-12-31'),
        );

        $this->assertSame($approverIdentityId, $created->assignedStage1ApproverIdentityId);
    }

    #[Test]
    public function it_throws_when_stage1_approver_cannot_be_resolved(): void
    {
        $employeeId = UuidGenerator::uuid7();

        $orgChart = Mockery::mock(Stage1ApproverIdentityReadContract::class);
        $orgChart->shouldReceive('resolveForEmployee')
            ->once()
            ->with($employeeId)
            ->andReturn(null);

        $codeRepo = Mockery::mock(RequestRepositoryContract::class);
        $codeRepo->shouldNotReceive('nextDailySequenceForUtcDate');
        $codeGenerator = new RequestCodeGenerator($codeRepo);

        $requests = Mockery::mock(RequestRepositoryContract::class);
        $requests->shouldNotReceive('save');

        $action = new CreatePersonalRequestAction(
            codeGenerator: $codeGenerator,
            requests: $requests,
            assignStage1Approver: new AssignStage1ApproverSnapshotAction($orgChart),
        );

        $this->expectException(Stage1ApproverUnresolvedException::class);

        $action->execute(
            employeeId: EmployeeReferenceId::fromString($employeeId),
            dormitoryId: DormitorySiteId::fromString(UuidGenerator::uuid7()),
            checkInDate: new DateTimeImmutable('2026-07-01'),
            checkOutDate: new DateTimeImmutable('2026-12-31'),
        );
    }
}
