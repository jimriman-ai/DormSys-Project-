<?php

declare(strict_types=1);

namespace App\Modules\Identity\Domain\Events;

use App\Support\Events\BaseEvent;
use DateTimeImmutable;

final class UserCreated extends BaseEvent
{
    public const string EVENT_NAME = 'identity.user.created';

    public const string VERSION = '1.0';

    public static function forUser(string $userId, ?DateTimeImmutable $occurredAt = null): self
    {
        $event = self::raise($userId, [
            'user_id' => $userId,
        ]);

        if ($occurredAt !== null) {
            return new self(
                eventId: $event->eventId,
                occurredAt: $occurredAt,
                aggregateId: $userId,
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
                'user_id' => $this->aggregateId,
                'occurred_at' => $this->occurredAt->format(DATE_ATOM),
            ],
        ];
    }
}
