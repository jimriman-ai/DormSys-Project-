<?php

declare(strict_types=1);

namespace App\Modules\Workflow\Domain\Entities;

use App\Modules\Workflow\Domain\Enums\RequestApprovalWorkflowStage;
use App\Modules\Workflow\Domain\Enums\WorkflowInstanceStatus;
use App\Modules\Workflow\Domain\Exceptions\InvalidWorkflowTransitionException;
use App\Modules\Workflow\Domain\Exceptions\UnauthorizedWorkflowStageActorException;
use App\Modules\Workflow\Domain\ValueObjects\IdentityUserId;
use App\Modules\Workflow\Domain\ValueObjects\RequestReferenceId;
use App\Modules\Workflow\Domain\ValueObjects\WorkflowInstanceId;
use DateTimeImmutable;

/**
 * Request Approval Workflow instance (OD-1 — not a generic multi-definition engine).
 */
final class RequestApprovalWorkflowInstance
{
    /**
     * @param  list<WorkflowStepExecution>  $steps
     */
    public function __construct(
        public readonly WorkflowInstanceId $id,
        public readonly RequestReferenceId $requestId,
        public private(set) WorkflowInstanceStatus $status,
        public readonly ?IdentityUserId $stage1ApproverIdentityId,
        public private(set) ?RequestApprovalWorkflowStage $currentStage,
        public private(set) array $steps,
        public readonly DateTimeImmutable $startedAt,
        public private(set) ?DateTimeImmutable $completedAt,
    ) {}

    public static function start(
        RequestReferenceId $requestId,
        ?IdentityUserId $stage1ApproverIdentityId,
        DateTimeImmutable $startedAt,
    ): self {
        $first = RequestApprovalWorkflowStage::DepartmentManager;
        $step = WorkflowStepExecution::activate($first, $startedAt);

        return new self(
            id: WorkflowInstanceId::generate(),
            requestId: $requestId,
            status: WorkflowInstanceStatus::Running,
            stage1ApproverIdentityId: $stage1ApproverIdentityId,
            currentStage: $first,
            steps: [$step],
            startedAt: $startedAt,
            completedAt: null,
        );
    }

    public function activeStep(): WorkflowStepExecution
    {
        foreach ($this->steps as $step) {
            if ($step->isPending()) {
                return $step;
            }
        }

        throw new InvalidWorkflowTransitionException('No pending workflow step.');
    }

    public function assertActorMayDecide(
        IdentityUserId $actorId,
        string $policyRoleForStage,
        bool $actorHasPolicyRole,
    ): void {
        $stage = $this->requireCurrentStage();

        if ($stage->isFirst()) {
            if ($this->stage1ApproverIdentityId === null) {
                throw new UnauthorizedWorkflowStageActorException('Stage-1 approver snapshot is missing.');
            }

            if (! $this->stage1ApproverIdentityId->equals($actorId)) {
                throw new UnauthorizedWorkflowStageActorException('Actor is not the assigned Stage-1 approver.');
            }

            return;
        }

        if (! $actorHasPolicyRole) {
            throw new UnauthorizedWorkflowStageActorException(
                sprintf('Actor lacks required role "%s" for stage "%s".', $policyRoleForStage, $stage->value),
            );
        }
    }

    /**
     * @return array{instance: self, completedStep: WorkflowStepExecution, activatedStage: ?RequestApprovalWorkflowStage}
     */
    public function approve(IdentityUserId $actorId, DateTimeImmutable $at): array
    {
        $this->assertRunning();
        $step = $this->activeStep();
        $stage = $step->stage;

        if ($this->currentStage !== $stage) {
            throw new InvalidWorkflowTransitionException('Active step does not match current stage.');
        }

        $step->completeApproved($actorId, $at);

        return $this->advanceAfterSuccessfulStep($step, $stage, $at);
    }

    /**
     * @return array{instance: self, completedStep: WorkflowStepExecution, activatedStage: ?RequestApprovalWorkflowStage}
     */
    public function autoApprove(IdentityUserId $systemActorId, DateTimeImmutable $at): array
    {
        $this->assertRunning();
        $step = $this->activeStep();
        $stage = $step->stage;

        $step->completeSkippedAuto($systemActorId, $at);

        return $this->advanceAfterSuccessfulStep($step, $stage, $at);
    }

    /**
     * @return array{instance: self, completedStep: WorkflowStepExecution}
     */
    public function reject(IdentityUserId $actorId, string $reason, DateTimeImmutable $at): array
    {
        $this->assertRunning();
        $step = $this->activeStep();
        $step->completeRejected($actorId, $reason, $at);

        $this->status = WorkflowInstanceStatus::Rejected;
        $this->currentStage = null;
        $this->completedAt = $at;

        return [
            'instance' => $this,
            'completedStep' => $step,
        ];
    }

    public function cancel(DateTimeImmutable $at): void
    {
        if ($this->status !== WorkflowInstanceStatus::Running
            && $this->status !== WorkflowInstanceStatus::Pending) {
            return;
        }

        foreach ($this->steps as $step) {
            $step->cancel($at);
        }

        $this->status = WorkflowInstanceStatus::Cancelled;
        $this->currentStage = null;
        $this->completedAt = $at;
    }

    public function requireCurrentStage(): RequestApprovalWorkflowStage
    {
        if ($this->currentStage === null) {
            throw new InvalidWorkflowTransitionException('Workflow has no current stage.');
        }

        return $this->currentStage;
    }

    /**
     * @return array{instance: self, completedStep: WorkflowStepExecution, activatedStage: ?RequestApprovalWorkflowStage}
     */
    private function advanceAfterSuccessfulStep(
        WorkflowStepExecution $completedStep,
        RequestApprovalWorkflowStage $completedStage,
        DateTimeImmutable $at,
    ): array {
        $next = $completedStage->next();

        if ($next === null) {
            $this->status = WorkflowInstanceStatus::Completed;
            $this->currentStage = null;
            $this->completedAt = $at;

            return [
                'instance' => $this,
                'completedStep' => $completedStep,
                'activatedStage' => null,
            ];
        }

        $newStep = WorkflowStepExecution::activate($next, $at);
        $this->steps[] = $newStep;
        $this->currentStage = $next;

        return [
            'instance' => $this,
            'completedStep' => $completedStep,
            'activatedStage' => $next,
        ];
    }

    private function assertRunning(): void
    {
        if ($this->status !== WorkflowInstanceStatus::Running) {
            throw new InvalidWorkflowTransitionException('Workflow instance is not running.');
        }
    }
}
