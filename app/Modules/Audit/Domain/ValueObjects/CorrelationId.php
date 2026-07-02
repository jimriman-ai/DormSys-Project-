<?php

declare(strict_types=1);

namespace App\Modules\Audit\Domain\ValueObjects;

use App\Support\Exceptions\ValidationException;

final readonly class CorrelationId
{
    public function __construct(
        public string $value,
    ) {
        if ($value === '' || strlen($value) > 191) {
            throw new ValidationException('Invalid correlation identifier.');
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
