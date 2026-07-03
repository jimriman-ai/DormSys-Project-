<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Application\DTOs;

use DateTimeImmutable;

final readonly class CorrelationProjectionEntryReadDto
{
    public function __construct(
        public string $sourceAuditLogId,
        public string $correlationId,
        public DateTimeImmutable $occurredAt,
        public string $entityType,
        public string $entityId,
        public string $actorType,
        public string $actorId,
        public string $eventType,
        public string $sourceContext,
        public DateTimeImmutable $ingestedAt,
    ) {}
}
