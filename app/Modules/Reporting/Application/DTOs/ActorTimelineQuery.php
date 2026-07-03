<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Application\DTOs;

use App\Support\Exceptions\ValidationException;
use DateTimeImmutable;
use Ramsey\Uuid\Uuid;

final readonly class ActorTimelineQuery
{
    /**
     * @param  list<string>|null  $eventTypes
     */
    public function __construct(
        public string $actorType,
        public string $actorId,
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

        if (! in_array($actorType, ['user', 'system'], true)) {
            throw new ValidationException('Invalid actor type.');
        }

        if (! Uuid::isValid($actorId)) {
            throw new ValidationException('Invalid actor identifier.');
        }
    }
}
