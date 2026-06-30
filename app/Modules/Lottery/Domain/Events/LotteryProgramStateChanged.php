<?php

declare(strict_types=1);

namespace App\Modules\Lottery\Domain\Events;

use App\Support\Events\BaseEvent;
use DateTimeImmutable;

final class LotteryProgramStateChanged extends BaseEvent
{
    public const string EVENT_NAME = 'lottery_program.state_changed';

    public const string VERSION = '1.0';

    public static function forProgram(
        string $programId,
        string $previousStatus,
        string $newStatus,
        ?DateTimeImmutable $occurredAt = null,
    ): self {
        $event = self::raise($programId, [
            'program_id' => $programId,
            'previous_status' => $previousStatus,
            'new_status' => $newStatus,
        ]);

        if ($occurredAt !== null) {
            return new self(
                eventId: $event->eventId,
                occurredAt: $occurredAt,
                aggregateId: $programId,
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
                'program_id' => $this->aggregateId,
                'previous_status' => $this->payload['previous_status'] ?? null,
                'new_status' => $this->payload['new_status'] ?? null,
                'occurred_at' => $this->occurredAt->format(DATE_ATOM),
            ],
        ];
    }
}
