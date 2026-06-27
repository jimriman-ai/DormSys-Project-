<?php

declare(strict_types=1);

namespace App\Modules\Request\Domain\ValueObjects;

use App\Support\Exceptions\ValidationException;
use Ramsey\Uuid\Uuid;

/**
 * Immutable reference to an approver identity user (R-03 — no FK).
 */
final readonly class ApproverReferenceId
{
    public function __construct(
        public string $value,
    ) {
        if (! Uuid::isValid($value)) {
            throw new ValidationException('Invalid approver reference identifier.');
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
