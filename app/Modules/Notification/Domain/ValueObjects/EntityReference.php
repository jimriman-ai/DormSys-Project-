<?php

declare(strict_types=1);

namespace App\Modules\Notification\Domain\ValueObjects;

use App\Support\Exceptions\ValidationException;
use Ramsey\Uuid\Uuid;

final readonly class EntityReference
{
    public function __construct(
        public string $entityType,
        public string $entityId,
    ) {
        if ($entityType === '' || strlen($entityType) > 64) {
            throw new ValidationException('Invalid entity type.');
        }

        if (! Uuid::isValid($entityId)) {
            throw new ValidationException('Invalid entity identifier.');
        }
    }

    public static function fromStrings(string $entityType, string $entityId): self
    {
        return new self($entityType, $entityId);
    }
}
