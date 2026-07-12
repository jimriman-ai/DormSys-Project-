<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Application\DTOs;

use App\Modules\Audit\Application\DTOs\AuditHistoryItemDto;
use DateTimeImmutable;

/**
 * Reporting-owned projection input shape.
 *
 * Keeps Reporting Infrastructure free of Audit Application imports while
 * Application materializers map from AuditHistoryItemDto at the boundary.
 */
final readonly class ProjectionSourceItemDto
{
    public function __construct(
        public string $auditLogId,
        public string $correlationId,
        public string $eventType,
        public string $entityType,
        public string $entityId,
        public string $actorType,
        public string $actorId,
        public string $sourceContext,
        public DateTimeImmutable $occurredAt,
    ) {}

    public static function fromAuditHistoryItem(AuditHistoryItemDto $item): self
    {
        return new self(
            auditLogId: $item->auditLogId,
            correlationId: $item->correlationId,
            eventType: $item->eventType,
            entityType: $item->entityType,
            entityId: $item->entityId,
            actorType: $item->actorType,
            actorId: $item->actorId,
            sourceContext: $item->sourceContext,
            occurredAt: $item->occurredAt,
        );
    }
}
