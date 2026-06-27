<?php

declare(strict_types=1);

namespace App\Modules\Request\Domain\Entities;

use App\Modules\Request\Domain\Enums\RequestType;
use App\Modules\Request\Domain\States\DraftState;
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

    public function requireId(): RequestId
    {
        if ($this->id === null) {
            throw new \LogicException('Request identifier is not assigned.');
        }

        return $this->id;
    }
}
