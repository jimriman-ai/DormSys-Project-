<?php

declare(strict_types=1);

namespace Tests\Support\Audit;

use App\Modules\Audit\Application\Contracts\AuditRecordingContract;
use App\Modules\Audit\Application\DTOs\AuditEntryDto;
use App\Modules\Audit\Domain\Enums\ActorType;
use App\Modules\Audit\Domain\Enums\AuditEventType;
use App\Modules\Audit\Domain\ValueObjects\ActorReference;
use App\Modules\Audit\Domain\ValueObjects\CorrelationId;
use App\Modules\Audit\Domain\ValueObjects\EntityReference;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use DateTimeImmutable;
use DateTimeZone;

/**
 * External producer test double — lives outside Audit module to verify R10 consumer path.
 */
final class AuditProducerTestDouble
{
    public function __construct(
        private readonly AuditRecordingContract $auditRecording,
    ) {}

    public function emitSampleEntry(
        ?string $correlationId = null,
        ?DateTimeImmutable $occurredAt = null,
        ?string $entityId = null,
    ): string {
        $entityId ??= UuidGenerator::uuid7();
        $correlationId ??= 'test-double:producer:'.$entityId;
        $occurredAt ??= new DateTimeImmutable('2026-07-02T12:00:00Z', new DateTimeZone('UTC'));

        $result = $this->auditRecording->record(new AuditEntryDto(
            correlationId: CorrelationId::fromString($correlationId),
            eventType: AuditEventType::RequestSubmitted,
            entityReference: EntityReference::fromStrings('request', $entityId),
            actorReference: new ActorReference(ActorType::System, 'system:scheduler'),
            sourceContext: 'test_double',
            oldValues: null,
            newValues: ['status' => 'submitted'],
            metadata: ['producer' => 'AuditProducerTestDouble'],
            occurredAt: $occurredAt,
        ));

        return $result->auditLogId;
    }
}
