<?php

declare(strict_types=1);

namespace App\Modules\Request\Domain\Events;

use App\Support\Events\BaseEvent;
use DateTimeImmutable;

final class RequestCancelled extends BaseEvent
{
    public const string EVENT_NAME = 'request.cancelled';

    public const string VERSION = '1.0';

    public static function forRequest(
        string $requestId,
        string $previousStatus,
        ?DateTimeImmutable $occurredAt = null,
    ): self {
        $event = self::raise($requestId, [
            'request_id' => $requestId,
            'previous_status' => $previousStatus,
        ]);

        if ($occurredAt !== null) {
            return new self(
                eventId: $event->eventId,
                occurredAt: $occurredAt,
                aggregateId: $requestId,
                payload: $event->payload,
            );
        }

        return $event;
    }

    /**
     * @return array<string, mixed>
     */
    public function toContractPayload(): array
    {
        return [
            'event' => self::EVENT_NAME,
            'version' => self::VERSION,
            'payload' => [
                'request_id' => $this->aggregateId,
                'previous_status' => $this->payload['previous_status'] ?? null,
                'occurred_at' => $this->occurredAt->format(DATE_ATOM),
            ],
        ];
    }
}
