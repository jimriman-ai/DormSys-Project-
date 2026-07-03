<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Application\DTOs;

use App\Support\Exceptions\ValidationException;
use DateTimeImmutable;
use Ramsey\Uuid\Uuid;

final readonly class EntityTimelineQuery
{
    /**
     * @param  list<string>|null  $eventTypes
     */
    public function __construct(
        public string $entityType,
        public string $entityId,
        public ?array $eventTypes = null,
        public ?DateTimeImmutable $occurredFrom = null,
        public ?DateTimeImmutable $occurredTo = null,
        public bool $includeArchived = false,
        public int $page = 1,
        public int $perPage = 50,
    ) {
        if ($perPage < 1) {
            throw new ValidationException('perPage must be at least 1.');
        }

        if ($perPage > 200) {
            throw new ValidationException('perPage must not exceed 200.');
        }

        if ($page < 1) {
            throw new ValidationException('page must be at least 1.');
        }

        if (! Uuid::isValid($entityId)) {
            throw new ValidationException('Invalid entity identifier.');
        }
    }
}
