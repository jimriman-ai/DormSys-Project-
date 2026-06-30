<?php

declare(strict_types=1);

namespace App\Modules\Lottery\Domain\Events;

use App\Support\Events\BaseEvent;
use DateTimeImmutable;

final class LotteryProgramCreated extends BaseEvent
{
    public const string EVENT_NAME = 'lottery_program.created';

    public const string VERSION = '1.0';

    public static function forProgram(
        string $programId,
        string $dormitoryId,
        int $capacity,
        ?DateTimeImmutable $occurredAt = null,
    ): self {
        $event = self::raise($programId, [
            'program_id' => $programId,
            'dormitory_id' => $dormitoryId,
            'capacity' => $capacity,
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
                'dormitory_id' => $this->payload['dormitory_id'] ?? null,
                'capacity' => $this->payload['capacity'] ?? null,
                'occurred_at' => $this->occurredAt->format(DATE_ATOM),
            ],
        ];
    }
}
