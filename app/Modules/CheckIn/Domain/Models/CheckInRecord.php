<?php

declare(strict_types=1);

namespace App\Modules\CheckIn\Domain\Models;

use App\Modules\CheckIn\Domain\ValueObjects\CheckInRecordId;
use DateTimeImmutable;

final class CheckInRecord
{
    public function __construct(
        public readonly ?CheckInRecordId $id,
        public readonly string $allocationId,
        public readonly DateTimeImmutable $checkedInAt,
        public ?DateTimeImmutable $checkedOutAt,
        public readonly string $operatorId,
    ) {}

    public function requireId(): CheckInRecordId
    {
        if ($this->id === null) {
            throw new \LogicException('Check-in record identifier is not assigned.');
        }

        return $this->id;
    }

    public function isCheckedOut(): bool
    {
        return $this->checkedOutAt !== null;
    }

    public static function open(
        string $allocationId,
        string $operatorId,
        DateTimeImmutable $checkedInAt,
    ): self {
        return new self(
            id: null,
            allocationId: $allocationId,
            checkedInAt: $checkedInAt,
            checkedOutAt: null,
            operatorId: $operatorId,
        );
    }

    public function withCheckOut(DateTimeImmutable $checkedOutAt): self
    {
        if ($this->checkedOutAt !== null) {
            throw new \LogicException('Check-in record is already checked out.');
        }

        return new self(
            id: $this->id,
            allocationId: $this->allocationId,
            checkedInAt: $this->checkedInAt,
            checkedOutAt: $checkedOutAt,
            operatorId: $this->operatorId,
        );
    }
}
