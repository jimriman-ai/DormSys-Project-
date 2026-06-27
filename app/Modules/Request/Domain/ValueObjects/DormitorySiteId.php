<?php

declare(strict_types=1);

namespace App\Modules\Request\Domain\ValueObjects;

use App\Support\Exceptions\ValidationException;
use Ramsey\Uuid\Uuid;

/**
 * Immutable reference to a dormitory site (R-03 — no FK).
 */
final readonly class DormitorySiteId
{
    public function __construct(
        public string $value,
    ) {
        if (! Uuid::isValid($value)) {
            throw new ValidationException('Invalid dormitory site identifier.');
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
