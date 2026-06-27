<?php

declare(strict_types=1);

namespace App\Modules\Request\Domain\Entities;

use App\Modules\Request\Domain\Enums\RequestType;
use App\Modules\Request\Domain\States\ApprovedState;
use App\Modules\Request\Domain\States\CancelledState;
use App\Modules\Request\Domain\States\DraftState;
use App\Modules\Request\Domain\States\PendingDepartmentManagerState;
use App\Modules\Request\Domain\States\PendingDormitoryManagerState;
use App\Modules\Request\Domain\States\PendingDormitoryUnitState;
use App\Modules\Request\Domain\States\PendingHRState;
use App\Modules\Request\Domain\States\RejectedState;
use App\Modules\Request\Domain\States\SubmittedState;
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
    ) {}

    public static function createDraft(
        RequestCode $code,
        EmployeeReferenceId $employeeId,
        DormitorySiteId $dormitoryId,
        RequestType $type,
        DateTimeImmutable $checkInDate,
        DateTimeImmutable $checkOutDate,
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
        );
    }

    public function assignId(RequestId $id): self
    {
        return new self(
            id: $id,
            code: $this->code,
            employeeId: $this->employeeId,
            dormitoryId: $this->dormitoryId,
            type: $this->type,
            checkInDate: $this->checkInDate,
            checkOutDate: $this->checkOutDate,
            status: $this->status,
            submittedAt: $this->submittedAt,
            cancelledAt: $this->cancelledAt,
            rejectionReason: $this->rejectionReason,
        );
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
            ApprovedState::$name,
            RejectedState::$name,
            CancelledState::$name,
        ], true);
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
        return new self(
            id: $this->id,
            code: $this->code,
            employeeId: $this->employeeId,
            dormitoryId: $this->dormitoryId,
            type: $this->type,
            checkInDate: $this->checkInDate,
            checkOutDate: $this->checkOutDate,
            status: $status,
            submittedAt: $this->submittedAt,
            cancelledAt: $this->cancelledAt,
            rejectionReason: $rejectionReason ?? $this->rejectionReason,
        );
    }

    public function markSubmitted(DateTimeImmutable $submittedAt): self
    {
        return new self(
            id: $this->id,
            code: $this->code,
            employeeId: $this->employeeId,
            dormitoryId: $this->dormitoryId,
            type: $this->type,
            checkInDate: $this->checkInDate,
            checkOutDate: $this->checkOutDate,
            status: PendingDepartmentManagerState::$name,
            submittedAt: $submittedAt,
            cancelledAt: $this->cancelledAt,
            rejectionReason: $this->rejectionReason,
        );
    }

    public function markCancelled(DateTimeImmutable $cancelledAt): self
    {
        return new self(
            id: $this->id,
            code: $this->code,
            employeeId: $this->employeeId,
            dormitoryId: $this->dormitoryId,
            type: $this->type,
            checkInDate: $this->checkInDate,
            checkOutDate: $this->checkOutDate,
            status: CancelledState::$name,
            submittedAt: $this->submittedAt,
            cancelledAt: $cancelledAt,
            rejectionReason: $this->rejectionReason,
        );
    }

    public function requireId(): RequestId
    {
        if ($this->id === null) {
            throw new \LogicException('Request identifier is not assigned.');
        }

        return $this->id;
    }
}
