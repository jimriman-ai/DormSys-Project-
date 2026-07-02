<?php

declare(strict_types=1);

namespace App\Modules\Audit\Application\DTOs;

use App\Modules\Audit\Domain\Enums\ActorType;
use App\Modules\Audit\Domain\Enums\AuditEventType;
use App\Modules\Audit\Domain\Enums\AuditRecordStatus;
use App\Modules\Audit\Domain\ValueObjects\ActorReference;
use App\Modules\Audit\Domain\ValueObjects\CorrelationId;
use App\Modules\Audit\Domain\ValueObjects\EntityReference;
use App\Support\Exceptions\ValidationException;
use DateTimeImmutable;
use Ramsey\Uuid\Uuid;

final readonly class AuditEntryDto
{
    /**
     * @param  array<string, mixed>|null  $oldValues
     * @param  array<string, mixed>|null  $newValues
     * @param  array<string, mixed>|null  $metadata
     */
    public function __construct(
        public CorrelationId $correlationId,
        public AuditEventType $eventType,
        public EntityReference $entityReference,
        public ActorReference $actorReference,
        public string $sourceContext,
        public ?array $oldValues,
        public ?array $newValues,
        public ?array $metadata,
        public DateTimeImmutable $occurredAt,
    ) {
        if ($sourceContext === '' || strlen($sourceContext) > 32) {
            throw new ValidationException('Source context is required.');
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromArray(array $payload): self
    {
        self::requireFields($payload, [
            'correlationId',
            'eventType',
            'entityType',
            'entityId',
            'actorType',
            'actorId',
            'sourceContext',
        ]);

        $actorType = ActorType::from((string) $payload['actorType']);

        return new self(
            correlationId: CorrelationId::fromString((string) $payload['correlationId']),
            eventType: AuditEventType::from((string) $payload['eventType']),
            entityReference: EntityReference::fromStrings(
                (string) $payload['entityType'],
                (string) $payload['entityId'],
            ),
            actorReference: new ActorReference($actorType, (string) $payload['actorId']),
            sourceContext: (string) $payload['sourceContext'],
            oldValues: self::optionalArray($payload['oldValues'] ?? null),
            newValues: self::optionalArray($payload['newValues'] ?? null),
            metadata: self::optionalArray($payload['metadata'] ?? null),
            occurredAt: self::parseOccurredAt($payload['occurredAt'] ?? null),
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  list<string>  $fields
     */
    private static function requireFields(array $payload, array $fields): void
    {
        foreach ($fields as $field) {
            if (! array_key_exists($field, $payload)) {
                throw new ValidationException('Missing required audit entry field: '.$field.'.');
            }
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function optionalArray(mixed $value): ?array
    {
        if ($value === null) {
            return null;
        }

        if (! is_array($value)) {
            throw new ValidationException('Snapshot values must be an object.');
        }

        return $value;
    }

    private static function parseOccurredAt(mixed $value): DateTimeImmutable
    {
        if ($value instanceof DateTimeImmutable) {
            return $value;
        }

        if (is_string($value) && $value !== '') {
            return new DateTimeImmutable($value);
        }

        return new DateTimeImmutable('now', new \DateTimeZone('UTC'));
    }
}

final readonly class AuditRecordResultDto
{
    public function __construct(
        public string $auditLogId,
        public AuditRecordStatus $status,
        public DateTimeImmutable $recordedAt,
    ) {}
}

final readonly class AuditHistoryQuery
{
    /**
     * @param  list<string>|null  $eventTypes
     */
    public function __construct(
        public ?string $entityType = null,
        public ?string $entityId = null,
        public ?string $actorType = null,
        public ?string $actorId = null,
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

        if ($entityId !== null && ! Uuid::isValid($entityId)) {
            throw new ValidationException('Invalid entity identifier filter.');
        }

        if (! $this->hasFilterDimension()) {
            throw new ValidationException('At least one filter dimension is required.');
        }
    }

    public function hasFilterDimension(): bool
    {
        if ($this->entityType !== null && $this->entityId !== null) {
            return true;
        }

        if ($this->actorType !== null && $this->actorId !== null) {
            return true;
        }

        if ($this->eventTypes !== null && $this->eventTypes !== []) {
            return true;
        }

        return false;
    }
}

final readonly class AuditHistoryItemDto
{
    /**
     * @param  array<string, mixed>|null  $oldValues
     * @param  array<string, mixed>|null  $newValues
     * @param  array<string, mixed>|null  $metadata
     */
    public function __construct(
        public string $auditLogId,
        public string $correlationId,
        public string $eventType,
        public string $entityType,
        public string $entityId,
        public string $actorType,
        public string $actorId,
        public string $sourceContext,
        public ?array $oldValues,
        public ?array $newValues,
        public ?array $metadata,
        public DateTimeImmutable $occurredAt,
        public DateTimeImmutable $createdAt,
    ) {}
}

final readonly class PaginatedAuditHistoryDto
{
    /**
     * @param  list<AuditHistoryItemDto>  $items
     */
    public function __construct(
        public array $items,
        public int $total,
        public int $page,
        public int $perPage,
        public int $lastPage,
    ) {}
}
