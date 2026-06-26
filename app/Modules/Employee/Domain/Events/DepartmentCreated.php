<?php

declare(strict_types=1);

namespace App\Modules\Employee\Domain\Events;

use App\Support\Events\BaseEvent;
use DateTimeImmutable;

final class DepartmentCreated extends BaseEvent
{
    public const string EVENT_NAME = 'employee.department.created';

    public const string VERSION = '1.0';

    public static function forDepartment(
        string $departmentId,
        string $code,
        ?DateTimeImmutable $occurredAt = null,
    ): self {
        $event = self::raise($departmentId, [
            'department_id' => $departmentId,
            'code' => $code,
        ]);

        if ($occurredAt !== null) {
            return new self(
                eventId: $event->eventId,
                occurredAt: $occurredAt,
                aggregateId: $departmentId,
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
                'department_id' => $this->aggregateId,
                'code' => $this->payload['code'] ?? null,
                'occurred_at' => $this->occurredAt->format(DATE_ATOM),
            ],
        ];
    }
}
