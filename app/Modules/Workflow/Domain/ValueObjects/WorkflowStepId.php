<?php

declare(strict_types=1);

namespace App\Modules\Workflow\Domain\ValueObjects;

use InvalidArgumentException;
use Ramsey\Uuid\Uuid;

final readonly class WorkflowStepId
{
    public function __construct(public string $value)
    {
        if ($value === '' || ! Uuid::isValid($value)) {
            throw new InvalidArgumentException('WorkflowStepId must be a valid UUID.');
        }
    }

    public static function generate(): self
    {
        return new self(Uuid::uuid7()->toString());
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }
}
