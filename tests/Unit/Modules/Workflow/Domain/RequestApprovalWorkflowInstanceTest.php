<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Workflow\Domain;

use App\Modules\Workflow\Domain\Entities\RequestApprovalWorkflowInstance;
use App\Modules\Workflow\Domain\Enums\RequestApprovalWorkflowStage;
use App\Modules\Workflow\Domain\Enums\WorkflowInstanceStatus;
use App\Modules\Workflow\Domain\Enums\WorkflowStepStatus;
use App\Modules\Workflow\Domain\Exceptions\UnauthorizedWorkflowStageActorException;
use App\Modules\Workflow\Domain\Services\FixedRequestApprovalStageRolePolicy;
use App\Modules\Workflow\Domain\ValueObjects\IdentityUserId;
use App\Modules\Workflow\Domain\ValueObjects\RequestReferenceId;
use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class RequestApprovalWorkflowInstanceTest extends TestCase
{
    #[Test]
    public function start_activates_department_manager_stage(): void
    {
        $now = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $instance = RequestApprovalWorkflowInstance::start(
            RequestReferenceId::fromString('11111111-1111-7111-8111-111111111111'),
            IdentityUserId::fromString('22222222-2222-7222-8222-222222222222'),
            $now,
        );

        $this->assertSame(WorkflowInstanceStatus::Running, $instance->status);
        $this->assertSame(RequestApprovalWorkflowStage::DepartmentManager, $instance->currentStage);
        $this->assertTrue($instance->activeStep()->isPending());
    }

    #[Test]
    public function od2_role_policy_is_fixed(): void
    {
        $policy = new FixedRequestApprovalStageRolePolicy;

        $this->assertSame('dormitory-manager', $policy->roleFor(RequestApprovalWorkflowStage::DepartmentManager));
        $this->assertSame('HR', $policy->roleFor(RequestApprovalWorkflowStage::HR));
        $this->assertSame('dormitory-manager', $policy->roleFor(RequestApprovalWorkflowStage::DormitoryManager));
        $this->assertSame('dormitory-unit', $policy->roleFor(RequestApprovalWorkflowStage::DormitoryUnit));
    }

    #[Test]
    public function stage1_rejects_non_assigned_actor(): void
    {
        $now = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $assigned = IdentityUserId::fromString('22222222-2222-7222-8222-222222222222');
        $other = IdentityUserId::fromString('33333333-3333-7333-8333-333333333333');

        $instance = RequestApprovalWorkflowInstance::start(
            RequestReferenceId::fromString('11111111-1111-7111-8111-111111111111'),
            $assigned,
            $now,
        );

        $this->expectException(UnauthorizedWorkflowStageActorException::class);
        $instance->assertActorMayDecide($other, 'dormitory-manager', true);
    }

    #[Test]
    public function approve_advances_and_final_completes(): void
    {
        $now = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $actor = IdentityUserId::fromString('22222222-2222-7222-8222-222222222222');
        $instance = RequestApprovalWorkflowInstance::start(
            RequestReferenceId::fromString('11111111-1111-7111-8111-111111111111'),
            $actor,
            $now,
        );

        $r1 = $instance->approve($actor, $now);
        $this->assertSame(RequestApprovalWorkflowStage::HR, $r1['activatedStage']);
        $this->assertSame(WorkflowStepStatus::Approved, $r1['completedStep']->status);

        $r2 = $instance->approve($actor, $now);
        $this->assertSame(RequestApprovalWorkflowStage::DormitoryManager, $r2['activatedStage']);

        $r3 = $instance->approve($actor, $now);
        $this->assertSame(RequestApprovalWorkflowStage::DormitoryUnit, $r3['activatedStage']);

        $r4 = $instance->approve($actor, $now);
        $this->assertNull($r4['activatedStage']);
        $this->assertSame(WorkflowInstanceStatus::Completed, $instance->status);
    }

    #[Test]
    public function reject_completes_instance_as_rejected(): void
    {
        $now = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $actor = IdentityUserId::fromString('22222222-2222-7222-8222-222222222222');
        $instance = RequestApprovalWorkflowInstance::start(
            RequestReferenceId::fromString('11111111-1111-7111-8111-111111111111'),
            $actor,
            $now,
        );

        $instance->reject($actor, 'policy', $now);

        $this->assertSame(WorkflowInstanceStatus::Rejected, $instance->status);
        $this->assertNull($instance->currentStage);
    }
}
