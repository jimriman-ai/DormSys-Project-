<?php

declare(strict_types=1);

namespace App\Support\Events;

use DateTimeImmutable;
use DateTimeZone;
use JsonSerializable;
use Ramsey\Uuid\Uuid;

/**
 * Base domain event with immutable metadata and serializable payload.
 */
abstract class BaseEvent implements JsonSerializable
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public readonly string $eventId,
        public readonly DateTimeImmutable $occurredAt,
        public readonly string $aggregateId,
        public readonly array $payload = [],
    ) {}

    /**
     * Create a new event occurrence for the given aggregate.
     *
     * @param  array<string, mixed>  $payload
     *
     * @phpstan-return static
     */
    public static function raise(string $aggregateId, array $payload = []): static
    {
        /** @phpstan-ignore new.static */
        return new static(
            eventId: Uuid::uuid7()->toString(),
            occurredAt: new DateTimeImmutable('now', new DateTimeZone('UTC')),
            aggregateId: $aggregateId,
            payload: $payload,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'event_id' => $this->eventId,
            'occurred_at' => $this->occurredAt->format(DATE_ATOM),
            'aggregate_id' => $this->aggregateId,
            'payload' => $this->payload,
            'event_type' => static::class,
        ];
    }
}
