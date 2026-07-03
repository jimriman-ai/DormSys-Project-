<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Application\DTOs;

use DateTimeImmutable;

final readonly class ActorActivitySummaryReadDto
{
    /**
     * @param  list<string>  $distinctEventTypes
     */
    public function __construct(
        public string $actorType,
        public string $actorId,
        public DateTimeImmutable $windowStart,
        public DateTimeImmutable $windowEnd,
        public int $eventCount,
        public array $distinctEventTypes,
        public int $distinctEntitiesTouched,
        public string $projectionVersion,
        public DateTimeImmutable $refreshedAt,
    ) {}
}
