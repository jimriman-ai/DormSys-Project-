<?php

declare(strict_types=1);

namespace App\Modules\Request\Domain\Entities;

use App\Modules\Request\Domain\Enums\RequestType;
use App\Modules\Request\Domain\Exceptions\InvalidRequestTransitionException;
use App\Modules\Request\Domain\States\AllocatedState;
use App\Modules\Request\Domain\States\AllocationFailedState;
use App\Modules\Request\Domain\States\ApprovedState;
use App\Modules\Request\Domain\States\CancelledState;
use App\Modules\Request\Domain\States\CheckedInState;
use App\Modules\Request\Domain\States\CheckedOutState;
use App\Modules\Request\Domain\States\DraftState;
use App\Modules\Request\Domain\States\PendingDepartmentManagerState;
use App\Modules\Request\Domain\States\PendingDormitoryManagerState;
use App\Modules\Request\Domain\States\PendingDormitoryUnitState;
use App\Modules\Request\Domain\States\PendingHRState;
use App\Modules\Request\Domain\States\RejectedState;
use App\Modules\Request\Domain\States\SubmittedState;
use App\Modules\Request\Domain\States\WaitingForAllocationState;
use App\Modules\Request\Domain\ValueObjects\DormitorySiteId;
use App\Modules\Request\Domain\ValueObjects\EmployeeReferenceId;
use App\Modules\Request\Domain\ValueObjects\RequestCode;
use App\Modules\Request\Domain\ValueObjects\RequestId;
use DateTimeImmutable;

final class Request
{
    public function __construct(
        public readonly ?RequestId $id,
        public readonly RequestCode $code,
        public readonly EmployeeReferenceId $employeeId,
        public readonly DormitorySiteId $dormitoryId,
        public readonly RequestType $type,
        public readonly DateTimeImmutable $checkInDate,
        public readonly DateTimeImmutable $checkOutDate,
        public string $status,
        public ?DateTimeImmutable $submittedAt = null,
        public ?DateTimeImmutable $cancelledAt = null,
        public ?string $rejectionReason = null,
        public ?string $assignedStage1ApproverIdentityId = null,
    ) {}

    public static function createDraft(
        RequestCode $code,
        EmployeeReferenceId $employeeId,
        DormitorySiteId $dormitoryId,
        RequestType $type,
        DateTimeImmutable $checkInDate,
        DateTimeImmutable $checkOutDate,
        ?string $assignedStage1ApproverIdentityId = null,
    ): self {
        return new self(
            id: null,
            code: $code,
            employeeId: $employeeId,
            dormitoryId: $dormitoryId,
            type: $type,
            checkInDate: $checkInDate,
            checkOutDate: $checkOutDate,
            status: DraftState::$name,
            assignedStage1ApproverIdentityId: $assignedStage1ApproverIdentityId,
        );
    }

    public function assignId(RequestId $id): self
    {
        return $this->copy(id: $id);
    }

    public function isDraft(): bool
    {
        return $this->status === DraftState::$name;
    }

    public function isSubmitted(): bool
    {
        return $this->status === SubmittedState::$name;
    }

    public function isCancellable(): bool
    {
        return $this->isDraft() || $this->isSubmitted();
    }

    public function isTerminal(): bool
    {
        return in_array($this->status, [
            RejectedState::$name,
            CancelledState::$name,
            AllocationFailedState::$name,
            CheckedOutState::$name,
        ], true);
    }

    public function isApproved(): bool
    {
        return $this->status === ApprovedState::$name;
    }

    public function isWaitingForAllocation(): bool
    {
        return $this->status === WaitingForAllocationState::$name;
    }

    public function isAllocated(): bool
    {
        return $this->status === AllocatedState::$name;
    }

    public function isCheckedIn(): bool
    {
        return $this->status === CheckedInState::$name;
    }

    public function isPendingApproval(): bool
    {
        return in_array($this->status, [
            PendingDepartmentManagerState::$name,
            PendingHRState::$name,
            PendingDormitoryManagerState::$name,
            PendingDormitoryUnitState::$name,
        ], true);
    }

    public function markApproved(): self
    {
        return $this->withStatus(ApprovedState::$name);
    }

    public function markRejected(string $reason): self
    {
        return $this->withStatus(RejectedState::$name, $reason);
    }

    public function withStatus(string $status, ?string $rejectionReason = null): self
    {
        return $this->copy(
            status: $status,
            rejectionReason: $rejectionReason ?? $this->rejectionReason,
        );
    }

    public function withAssignedStage1ApproverIdentityId(string $identityId): self
    {
        return $this->copy(assignedStage1ApproverIdentityId: $identityId);
    }

    public function markSubmitted(DateTimeImmutable $submittedAt): self
    {
        return $this->copy(
            status: PendingDepartmentManagerState::$name,
            submittedAt: $submittedAt,
        );
    }

    public function markCancelled(DateTimeImmutable $cancelledAt): self
    {
        return $this->copy(
            status: CancelledState::$name,
            cancelledAt: $cancelledAt,
        );
    }

    /**
     * OA-05-03: approved → waiting_for_allocation.
     */
    public function markWaitingForAllocation(): self
    {
        if (! $this->isApproved()) {
            throw new InvalidRequestTransitionException(
                sprintf('Cannot mark waiting_for_allocation from status "%s".', $this->status),
            );
        }

        return $this->withStatus(WaitingForAllocationState::$name);
    }

    /**
     * OA-05-03: waiting_for_allocation → allocated.
     */
    public function markAllocated(): self
    {
        if (! $this->isWaitingForAllocation()) {
            throw new InvalidRequestTransitionException(
                sprintf('Cannot mark allocated from status "%s".', $this->status),
            );
        }

        return $this->withStatus(AllocatedState::$name);
    }

    /**
     * OA-05-03: waiting_for_allocation|allocated → allocation_failed.
     */
    public function markAllocationFailed(string $reason): self
    {
        if (! $this->isWaitingForAllocation() && ! $this->isAllocated()) {
            throw new InvalidRequestTransitionException(
                sprintf('Cannot mark allocation_failed from status "%s".', $this->status),
            );
        }

        return $this->withStatus(AllocationFailedState::$name, $reason);
    }

    /**
     * OA-05-03: allocated → checked_in.
     *
     * CheckIn consumer wiring deferred — DEBT-W3-01 (docs/audit/wave3-debt-discovery.md).
     */
    public function markCheckedIn(): self
    {
        if (! $this->isAllocated()) {
            throw new InvalidRequestTransitionException(
                sprintf('Cannot mark checked_in from status "%s".', $this->status),
            );
        }

        return $this->withStatus(CheckedInState::$name);
    }

    /**
     * OA-05-03: checked_in → checked_out.
     *
     * CheckIn consumer wiring deferred — DEBT-W3-01 (docs/audit/wave3-debt-discovery.md).
     * Expiry: when CheckIn→Request lifecycle port is authorized (Lead HD).
     */
    public function markCheckedOut(): self
    {
        if (! $this->isCheckedIn()) {
            throw new InvalidRequestTransitionException(
                sprintf('Cannot mark checked_out from status "%s".', $this->status),
            );
        }

        return $this->withStatus(CheckedOutState::$name);
    }

    public function requireId(): RequestId
    {
        if ($this->id === null) {
            throw new \LogicException('Request identifier is not assigned.');
        }

        return $this->id;
    }

    private function copy(
        ?RequestId $id = null,
        ?string $status = null,
        ?DateTimeImmutable $submittedAt = null,
        ?DateTimeImmutable $cancelledAt = null,
        ?string $rejectionReason = null,
        ?string $assignedStage1ApproverIdentityId = null,
        bool $clearRejectionReason = false,
    ): self {
        return new self(
            id: $id ?? $this->id,
            code: $this->code,
            employeeId: $this->employeeId,
            dormitoryId: $this->dormitoryId,
            type: $this->type,
            checkInDate: $this->checkInDate,
            checkOutDate: $this->checkOutDate,
            status: $status ?? $this->status,
            submittedAt: $submittedAt ?? $this->submittedAt,
            cancelledAt: $cancelledAt ?? $this->cancelledAt,
            rejectionReason: $clearRejectionReason ? null : ($rejectionReason ?? $this->rejectionReason),
            assignedStage1ApproverIdentityId: $assignedStage1ApproverIdentityId ?? $this->assignedStage1ApproverIdentityId,
        );
    }
}
