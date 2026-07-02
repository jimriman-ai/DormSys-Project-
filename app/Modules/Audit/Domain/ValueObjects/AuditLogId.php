<?php

declare(strict_types=1);

namespace App\Modules\Audit\Domain\ValueObjects;

use App\Support\Exceptions\ValidationException;
use Ramsey\Uuid\Uuid;

final readonly class AuditLogId
{
    public function __construct(
        public string $value,
    ) {
        if (! Uuid::isValid($value)) {
            throw new ValidationException('Invalid audit log identifier.');
        }
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
