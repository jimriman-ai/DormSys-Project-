<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Application\DTOs;

use DateTimeImmutable;

final readonly class AuditWindowBucketDto
{
    /**
     * @param  array<string, int>|null  $topEventTypes
     */
    public function __construct(
        public DateTimeImmutable $windowStart,
        public DateTimeImmutable $windowEnd,
        public ?string $eventType,
        public ?string $sourceContext,
        public ?string $actorType,
        public ?string $entityType,
        public int $eventCount,
        public int $distinctEntityCount,
        public int $distinctActorCount,
        public ?array $topEventTypes,
    ) {}
}
