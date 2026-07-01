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
}
