<?php

declare(strict_types=1);

namespace App\Modules\Workflow\Domain\ValueObjects;

use InvalidArgumentException;
use Ramsey\Uuid\Uuid;

final readonly class IdentityUserId
{
    public function __construct(public string $value)
    {
        if ($value === '' || ! Uuid::isValid($value)) {
            throw new InvalidArgumentException('IdentityUserId must be a valid UUID.');
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
}
