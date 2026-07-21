<?php

declare(strict_types=1);

namespace App\Modules\Workflow\Domain\ValueObjects;

use InvalidArgumentException;
use Ramsey\Uuid\Uuid;

/** UUID reference to a Request aggregate (no Request module import). */
final readonly class RequestReferenceId
{
    public function __construct(public string $value)
    {
        if ($value === '' || ! Uuid::isValid($value)) {
            throw new InvalidArgumentException('RequestReferenceId must be a valid UUID.');
        }
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }
}
