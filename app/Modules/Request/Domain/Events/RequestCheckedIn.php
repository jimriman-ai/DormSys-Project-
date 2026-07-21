<?php

declare(strict_types=1);

namespace App\Modules\Request\Domain\Events;

use App\Support\Events\BaseEvent;

/**
 * Domain event for Request status checked_in (OA-05-03).
 *
 * CheckIn module does not yet invoke Request lifecycle — DEBT-W3-01 until wired.
 */
final class RequestCheckedIn extends BaseEvent
{
    public const string EVENT_NAME = 'request.checked_in';

    public const string VERSION = '1.0';

    public static function forRequest(string $requestId): self
    {
        return self::raise($requestId, [
            'request_id' => $requestId,
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
                'occurred_at' => $this->occurredAt->format(DATE_ATOM),
            ],
        ];
    }
}
