<?php

declare(strict_types=1);

namespace App\Modules\Workflow\Domain\Entities;

use App\Modules\Workflow\Domain\Enums\RequestApprovalWorkflowStage;
use App\Modules\Workflow\Domain\Enums\WorkflowStepStatus;
use App\Modules\Workflow\Domain\Exceptions\InvalidWorkflowTransitionException;
use App\Modules\Workflow\Domain\ValueObjects\IdentityUserId;
use App\Modules\Workflow\Domain\ValueObjects\WorkflowStepId;
use DateTimeImmutable;

/**
 * Orchestration audit step (OD-3 — not product-visible RequestApproval history).
 */
final class WorkflowStepExecution
{
    public function __construct(
        public readonly WorkflowStepId $id,
        public readonly RequestApprovalWorkflowStage $stage,
        public private(set) WorkflowStepStatus $status,
        public private(set) ?IdentityUserId $actorId,
        public private(set) ?string $reason,
        public readonly DateTimeImmutable $activatedAt,
        public private(set) ?DateTimeImmutable $completedAt,
    ) {}

    public static function activate(
        RequestApprovalWorkflowStage $stage,
        DateTimeImmutable $activatedAt,
    ): self {
        return new self(
            id: WorkflowStepId::generate(),
            stage: $stage,
            status: WorkflowStepStatus::Pending,
            actorId: null,
            reason: null,
            activatedAt: $activatedAt,
            completedAt: null,
        );
    }

    public function isPending(): bool
    {
        return $this->status === WorkflowStepStatus::Pending;
    }

    public function completeApproved(IdentityUserId $actorId, DateTimeImmutable $at): void
    {
        $this->assertPending();
        $this->status = WorkflowStepStatus::Approved;
        $this->actorId = $actorId;
        $this->completedAt = $at;
    }

    public function completeSkippedAuto(IdentityUserId $systemActorId, DateTimeImmutable $at): void
    {
        $this->assertPending();
        $this->status = WorkflowStepStatus::SkippedAuto;
        $this->actorId = $systemActorId;
        $this->completedAt = $at;
    }

    public function completeRejected(IdentityUserId $actorId, string $reason, DateTimeImmutable $at): void
    {
        $this->assertPending();

        if (trim($reason) === '') {
            throw new InvalidWorkflowTransitionException('Rejection reason is required.');
        }

        $this->status = WorkflowStepStatus::Rejected;
        $this->actorId = $actorId;
        $this->reason = $reason;
        $this->completedAt = $at;
    }

    public function cancel(DateTimeImmutable $at): void
    {
        if ($this->status !== WorkflowStepStatus::Pending) {
            return;
        }

        $this->status = WorkflowStepStatus::Cancelled;
        $this->completedAt = $at;
    }

    private function assertPending(): void
    {
        if (! $this->isPending()) {
            throw new InvalidWorkflowTransitionException('Workflow step is not pending.');
        }
    }
}
