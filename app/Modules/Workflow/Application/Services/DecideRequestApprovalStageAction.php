<?php

declare(strict_types=1);

namespace App\Modules\Workflow\Application\Services;

use App\Modules\Workflow\Application\Contracts\RequestApprovalCommandPort;
use App\Modules\Workflow\Application\Contracts\RequestApprovalWorkflowRepositoryContract;
use App\Modules\Workflow\Application\Contracts\StageRoleAuthorizationPort;
use App\Modules\Workflow\Application\DTOs\DecideRequestApprovalStageCommand;
use App\Modules\Workflow\Application\DTOs\WorkflowInstanceResult;
use App\Modules\Workflow\Application\Support\WorkflowInstanceResultFactory;
use App\Modules\Workflow\Domain\Enums\StageDecision;
use App\Modules\Workflow\Domain\Enums\WorkflowInstanceStatus;
use App\Modules\Workflow\Domain\Enums\WorkflowStepStatus;
use App\Modules\Workflow\Domain\Events\WorkflowInstanceCompleted;
use App\Modules\Workflow\Domain\Events\WorkflowInstanceRejected;
use App\Modules\Workflow\Domain\Events\WorkflowStepActivated;
use App\Modules\Workflow\Domain\Events\WorkflowStepCompleted;
use App\Modules\Workflow\Domain\Exceptions\InvalidWorkflowTransitionException;
use App\Modules\Workflow\Domain\Exceptions\WorkflowInstanceNotFoundException;
use App\Modules\Workflow\Domain\Services\FixedRequestApprovalStageRolePolicy;
use App\Modules\Workflow\Domain\ValueObjects\IdentityUserId;
use App\Modules\Workflow\Domain\ValueObjects\RequestReferenceId;
use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Support\Facades\Event;

/**
 * Records a human stage decision; delegates canonical RequestApproval write via port (OD-3).
 * Chains auto-approvals when configured. No dual-run / Request cutover (OD-4 / WP-WF-04).
 */
final class DecideRequestApprovalStageAction
{
    public function __construct(
        private readonly RequestApprovalWorkflowRepositoryContract $workflows,
        private readonly RequestApprovalCommandPort $requestApprovals,
        private readonly StageRoleAuthorizationPort $roleAuthorization,
        private readonly FixedRequestApprovalStageRolePolicy $stageRoles,
        private readonly ApplyRequestApprovalAutoApprovalsAction $autoApprovals,
    ) {}

    public function execute(DecideRequestApprovalStageCommand $command): WorkflowInstanceResult
    {
        $decision = StageDecision::tryFrom($command->decision)
            ?? throw new InvalidWorkflowTransitionException('Decision must be approved or rejected.');

        $requestId = RequestReferenceId::fromString($command->requestId);
        $actorId = IdentityUserId::fromString($command->actorIdentityId);
        $instance = $this->workflows->findRunningByRequestId($requestId);

        if ($instance === null) {
            throw new WorkflowInstanceNotFoundException('No running Request Approval Workflow for request.');
        }

        $stage = $instance->requireCurrentStage();
        $policyRole = $this->stageRoles->roleFor($stage);
        $hasRole = $this->roleAuthorization->identityHasPolicyRole($actorId->value, $policyRole);
        $instance->assertActorMayDecide($actorId, $policyRole, $hasRole);

        $now = new DateTimeImmutable('now', new DateTimeZone('UTC'));

        if ($decision === StageDecision::Rejected) {
            $reason = $command->reason ?? '';
            $result = $instance->reject($actorId, $reason, $now);
            $completed = $result['completedStep'];

            $this->requestApprovals->recordStageRejected(
                $instance->requestId->value,
                $completed->stage->value,
                $actorId->value,
                $reason,
                $now,
            );

            $this->workflows->save($instance);

            Event::dispatch(WorkflowStepCompleted::forStep(
                $instance->id->value,
                $instance->requestId->value,
                $completed->stage->value,
                WorkflowStepStatus::Rejected->value,
                $actorId->value,
            ));
            Event::dispatch(WorkflowInstanceRejected::forInstance(
                $instance->id->value,
                $instance->requestId->value,
                $completed->stage->value,
                $reason,
            ));

            return WorkflowInstanceResultFactory::fromInstance($instance);
        }

        $result = $instance->approve($actorId, $now);
        $completed = $result['completedStep'];

        $this->requestApprovals->recordStageApproved(
            $instance->requestId->value,
            $completed->stage->value,
            $actorId->value,
            $now,
        );

        Event::dispatch(WorkflowStepCompleted::forStep(
            $instance->id->value,
            $instance->requestId->value,
            $completed->stage->value,
            WorkflowStepStatus::Approved->value,
            $actorId->value,
        ));

        if ($result['activatedStage'] !== null) {
            Event::dispatch(WorkflowStepActivated::forStep(
                $instance->id->value,
                $instance->requestId->value,
                $result['activatedStage']->value,
            ));
        }

        if ($instance->status === WorkflowInstanceStatus::Completed) {
            Event::dispatch(WorkflowInstanceCompleted::forInstance(
                $instance->id->value,
                $instance->requestId->value,
            ));
            $this->workflows->save($instance);

            return WorkflowInstanceResultFactory::fromInstance($instance);
        }

        $this->workflows->save($instance);

        return $this->autoApprovals->execute($instance->requestId->value);
    }
}
