<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Workflow\Application;

use App\Modules\Workflow\Application\Contracts\RequestApprovalAutoSettingsPort;
use App\Modules\Workflow\Application\Contracts\RequestApprovalCommandPort;
use App\Modules\Workflow\Application\Contracts\StageRoleAuthorizationPort;
use App\Modules\Workflow\Application\DTOs\DecideRequestApprovalStageCommand;
use App\Modules\Workflow\Application\DTOs\StartRequestApprovalWorkflowCommand;
use App\Modules\Workflow\Application\Services\ApplyRequestApprovalAutoApprovalsAction;
use App\Modules\Workflow\Application\Services\DecideRequestApprovalStageAction;
use App\Modules\Workflow\Application\Services\StartRequestApprovalWorkflowAction;
use App\Modules\Workflow\Domain\Enums\RequestApprovalWorkflowStage;
use App\Modules\Workflow\Domain\Enums\WorkflowInstanceStatus;
use App\Modules\Workflow\Domain\Services\FixedRequestApprovalStageRolePolicy;
use DateTimeImmutable;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Unit\Modules\Workflow\Support\InMemoryRequestApprovalWorkflowRepository;

final class RequestApprovalWorkflowActionsTest extends TestCase
{
    #[Test]
    public function start_and_approve_records_canonical_request_approval_via_port(): void
    {
        Event::fake();

        $repo = new InMemoryRequestApprovalWorkflowRepository;

        $commands = new class implements RequestApprovalCommandPort
        {
            /** @var list<array<string, mixed>> */
            public array $recorded = [];

            public function recordStageApproved(
                string $requestId,
                string $stage,
                string $approverIdentityId,
                DateTimeImmutable $decidedAt,
            ): void {
                $this->recorded[] = compact('requestId', 'stage', 'approverIdentityId') + ['decision' => 'approved'];
            }

            public function recordStageRejected(
                string $requestId,
                string $stage,
                string $approverIdentityId,
                string $reason,
                DateTimeImmutable $decidedAt,
            ): void {
                $this->recorded[] = compact('requestId', 'stage', 'approverIdentityId', 'reason') + ['decision' => 'rejected'];
            }
        };

        $roles = new class implements StageRoleAuthorizationPort
        {
            public function identityHasPolicyRole(string $identityUserId, string $policyRole): bool
            {
                return true;
            }
        };

        $auto = new class implements RequestApprovalAutoSettingsPort
        {
            public function isAutoApprovalEnabled(RequestApprovalWorkflowStage $stage): bool
            {
                return false;
            }
        };

        $autoAction = new ApplyRequestApprovalAutoApprovalsAction($repo, $commands, $auto);
        $decide = new DecideRequestApprovalStageAction(
            $repo,
            $commands,
            $roles,
            new FixedRequestApprovalStageRolePolicy,
            $autoAction,
        );
        $start = new StartRequestApprovalWorkflowAction($repo);

        $requestId = '11111111-1111-7111-8111-111111111111';
        $actorId = '22222222-2222-7222-8222-222222222222';

        $started = $start->execute(new StartRequestApprovalWorkflowCommand($requestId, $actorId));
        $this->assertSame(WorkflowInstanceStatus::Running->value, $started->status);
        $this->assertSame(RequestApprovalWorkflowStage::DepartmentManager->value, $started->currentStage);

        $after = $decide->execute(new DecideRequestApprovalStageCommand(
            requestId: $requestId,
            actorIdentityId: $actorId,
            decision: 'approved',
        ));

        $this->assertSame(RequestApprovalWorkflowStage::HR->value, $after->currentStage);
        $this->assertCount(1, $commands->recorded);
        $this->assertSame('approved', $commands->recorded[0]['decision']);
        $this->assertSame('department_manager', $commands->recorded[0]['stage']);
    }

    #[Test]
    public function auto_approval_chain_uses_skipped_auto_audit_and_request_port(): void
    {
        Event::fake();

        $repo = new InMemoryRequestApprovalWorkflowRepository;

        $commands = new class implements RequestApprovalCommandPort
        {
            /** @var list<array<string, mixed>> */
            public array $recorded = [];

            public function recordStageApproved(
                string $requestId,
                string $stage,
                string $approverIdentityId,
                DateTimeImmutable $decidedAt,
            ): void {
                $this->recorded[] = ['stage' => $stage, 'approver' => $approverIdentityId];
            }

            public function recordStageRejected(
                string $requestId,
                string $stage,
                string $approverIdentityId,
                string $reason,
                DateTimeImmutable $decidedAt,
            ): void {}
        };

        $roles = new class implements StageRoleAuthorizationPort
        {
            public function identityHasPolicyRole(string $identityUserId, string $policyRole): bool
            {
                return true;
            }
        };

        $auto = new class implements RequestApprovalAutoSettingsPort
        {
            public function isAutoApprovalEnabled(RequestApprovalWorkflowStage $stage): bool
            {
                return $stage !== RequestApprovalWorkflowStage::DepartmentManager;
            }
        };

        $autoAction = new ApplyRequestApprovalAutoApprovalsAction($repo, $commands, $auto);
        $decide = new DecideRequestApprovalStageAction(
            $repo,
            $commands,
            $roles,
            new FixedRequestApprovalStageRolePolicy,
            $autoAction,
        );
        $start = new StartRequestApprovalWorkflowAction($repo);

        $requestId = '11111111-1111-7111-8111-111111111111';
        $actorId = '22222222-2222-7222-8222-222222222222';

        $start->execute(new StartRequestApprovalWorkflowCommand($requestId, $actorId));
        $result = $decide->execute(new DecideRequestApprovalStageCommand(
            requestId: $requestId,
            actorIdentityId: $actorId,
            decision: 'approved',
        ));

        $this->assertSame(WorkflowInstanceStatus::Completed->value, $result->status);
        $this->assertCount(4, $commands->recorded);
        $this->assertSame('department_manager', $commands->recorded[0]['stage']);
        $this->assertSame($actorId, $commands->recorded[0]['approver']);
        $this->assertSame('hr', $commands->recorded[1]['stage']);
    }
}
