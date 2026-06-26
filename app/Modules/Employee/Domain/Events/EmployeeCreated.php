<?php

declare(strict_types=1);

namespace App\Modules\Employee\Domain\Events;

use App\Support\Events\BaseEvent;
use DateTimeImmutable;

final class EmployeeCreated extends BaseEvent
{
    public const string EVENT_NAME = 'employee.created';

    public const string VERSION = '1.0';

    public static function forEmployee(
        string $employeeId,
        string $identityId,
        ?DateTimeImmutable $occurredAt = null,
    ): self {
        $event = self::raise($employeeId, [
            'employee_id' => $employeeId,
            'identity_id' => $identityId,
        ]);

        if ($occurredAt !== null) {
            return new self(
                eventId: $event->eventId,
                occurredAt: $occurredAt,
                aggregateId: $employeeId,
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
                'employee_id' => $this->aggregateId,
                'identity_id' => $this->payload['identity_id'] ?? null,
                'occurred_at' => $this->occurredAt->format(DATE_ATOM),
            ],
        ];
    }
}
