<?php

declare(strict_types=1);

namespace App\Modules\Request\Domain\Events;

use App\Support\Events\BaseEvent;

final class RequestAllocationFailed extends BaseEvent
{
    public const string EVENT_NAME = 'request.allocation_failed';

    public const string VERSION = '1.0';

    public static function forRequest(string $requestId, string $reason): self
    {
        return self::raise($requestId, [
            'request_id' => $requestId,
            'reason' => $reason,
        ]);
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
                'reason' => $this->payload['reason'] ?? null,
                'occurred_at' => $this->occurredAt->format(DATE_ATOM),
            ],
        ];
    }
}
