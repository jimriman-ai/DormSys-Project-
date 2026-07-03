<?php

declare(strict_types=1);

namespace App\Modules\Audit\Domain\Models;

use App\Modules\Audit\Domain\Enums\AuditEventType;
use App\Modules\Audit\Domain\ValueObjects\ActorReference;
use App\Modules\Audit\Domain\ValueObjects\AuditLogId;
use App\Modules\Audit\Domain\ValueObjects\CorrelationId;
use App\Modules\Audit\Domain\ValueObjects\EntityReference;
use DateTimeImmutable;

final class AuditLog
{
    /**
     * @param  array<string, mixed>|null  $oldValues
     * @param  array<string, mixed>|null  $newValues
     * @param  array<string, mixed>|null  $metadata
     */
    public function __construct(
        public readonly ?AuditLogId $id,
        public readonly CorrelationId $correlationId,
        public readonly AuditEventType $eventType,
        public readonly EntityReference $entityReference,
        public readonly ActorReference $actorReference,
        public readonly string $sourceContext,
        public readonly ?array $oldValues,
        public readonly ?array $newValues,
        public readonly ?array $metadata,
        public readonly string $payloadHash,
        public readonly DateTimeImmutable $occurredAt,
        public readonly ?DateTimeImmutable $archivedAt,
        public readonly DateTimeImmutable $createdAt,
    ) {}

    public function requireId(): AuditLogId
    {
        if ($this->id === null) {
            throw new \LogicException('Audit log identifier is not assigned.');
        }

        return $this->id;
    }

    public function isArchived(): bool
    {
        return $this->archivedAt !== null;
    }

    /**
     * @param  array<string, mixed>|null  $oldValues
     * @param  array<string, mixed>|null  $newValues
     * @param  array<string, mixed>|null  $metadata
     */
    public static function record(
        CorrelationId $correlationId,
        AuditEventType $eventType,
        EntityReference $entityReference,
        ActorReference $actorReference,
        string $sourceContext,
        ?array $oldValues,
        ?array $newValues,
        ?array $metadata,
        string $payloadHash,
        DateTimeImmutable $occurredAt,
        DateTimeImmutable $createdAt,
        ?AuditLogId $id = null,
    ): self {
        return new self(
            id: $id,
            correlationId: $correlationId,
            eventType: $eventType,
            entityReference: $entityReference,
            actorReference: $actorReference,
            sourceContext: $sourceContext,
            oldValues: $oldValues,
            newValues: $newValues,
            metadata: $metadata,
            payloadHash: $payloadHash,
            occurredAt: $occurredAt,
            archivedAt: null,
            createdAt: $createdAt,
        );
    }
}
