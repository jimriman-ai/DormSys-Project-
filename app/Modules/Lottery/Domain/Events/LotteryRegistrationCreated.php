<?php

declare(strict_types=1);

namespace App\Modules\Lottery\Domain\Events;

use App\Support\Events\BaseEvent;
use DateTimeImmutable;

final class LotteryRegistrationCreated extends BaseEvent
{
    public const string EVENT_NAME = 'lottery_registration.created';

    public const string VERSION = '1.0';

    public static function forRegistration(
        string $registrationId,
        string $programId,
        string $requestId,
        string $employeeId,
        ?DateTimeImmutable $occurredAt = null,
    ): self {
        $event = self::raise($registrationId, [
            'registration_id' => $registrationId,
            'program_id' => $programId,
            'request_id' => $requestId,
            'employee_id' => $employeeId,
        ]);

        if ($occurredAt !== null) {
            return new self(
                eventId: $event->eventId,
                occurredAt: $occurredAt,
                aggregateId: $registrationId,
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
                'registration_id' => $this->aggregateId,
                'program_id' => $this->payload['program_id'] ?? null,
                'request_id' => $this->payload['request_id'] ?? null,
                'employee_id' => $this->payload['employee_id'] ?? null,
                'occurred_at' => $this->occurredAt->format(DATE_ATOM),
            ],
        ];
    }
}
