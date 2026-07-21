<?php

declare(strict_types=1);

namespace App\Modules\Request\Domain\Events;

use App\Support\Events\BaseEvent;

final class RequestAllocated extends BaseEvent
{
    public const string EVENT_NAME = 'request.allocated';

    public const string VERSION = '1.0';

    public static function forRequest(string $requestId, string $allocationId): self
    {
        return self::raise($requestId, [
            'request_id' => $requestId,
            'allocation_id' => $allocationId,
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
                'allocation_id' => $this->payload['allocation_id'] ?? null,
                'occurred_at' => $this->occurredAt->format(DATE_ATOM),
            ],
        ];
    }
}
