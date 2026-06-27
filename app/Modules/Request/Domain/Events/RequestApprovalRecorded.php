<?php

declare(strict_types=1);

namespace App\Modules\Request\Domain\Events;

use App\Support\Events\BaseEvent;

final class RequestApprovalRecorded extends BaseEvent
{
    public const string EVENT_NAME = 'request.approval_recorded';

    public const string VERSION = '1.0';

    /**
     * @param  array<string, mixed>  $approvalPayload
     */
    public static function forApproval(string $requestId, array $approvalPayload): self
    {
        return self::raise($requestId, array_merge(
            ['request_id' => $requestId],
            $approvalPayload,
        ));
    }

    /**
     * @return array<string, mixed>
     */
    public function toContractPayload(): array
    {
        return [
            'event' => self::EVENT_NAME,
            'version' => self::VERSION,
            'payload' => array_merge(
                ['request_id' => $this->aggregateId],
                $this->payload,
                ['occurred_at' => $this->occurredAt->format(DATE_ATOM)],
            ),
        ];
    }
}
