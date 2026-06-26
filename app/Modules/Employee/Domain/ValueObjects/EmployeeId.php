<?php

declare(strict_types=1);

namespace App\Modules\Employee\Domain\ValueObjects;

use App\Support\Exceptions\ValidationException;
use Ramsey\Uuid\Uuid;

final readonly class EmployeeId
{
    public function __construct(
        public string $value,
    ) {
        if (! Uuid::isValid($value)) {
            throw new ValidationException('Invalid employee identifier.');
        }
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
