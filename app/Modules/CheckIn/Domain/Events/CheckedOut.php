<?php

declare(strict_types=1);

namespace App\Modules\CheckIn\Domain\Events;

use App\Modules\CheckIn\Domain\Models\CheckInRecord;
use App\Support\Events\BaseEvent;

final class CheckedOut extends BaseEvent
{
    public const string EVENT_NAME = 'check_in.checked_out';

    public const string VERSION = '1.0';

    public static function forRecord(CheckInRecord $record): self
    {
        $checkedOutAt = $record->checkedOutAt;

        if ($checkedOutAt === null) {
            throw new \LogicException('Check-out timestamp is required for CheckedOut event.');
        }

        return self::raise($record->requireId()->value, [
            'allocation_id' => $record->allocationId,
            'operator_id' => $record->operatorId,
            'checked_in_at' => $record->checkedInAt->format(DATE_ATOM),
            'checked_out_at' => $checkedOutAt->format(DATE_ATOM),
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
            'payload' => array_merge(
                ['check_in_record_id' => $this->aggregateId],
                $this->payload,
                ['occurred_at' => $this->occurredAt->format(DATE_ATOM)],
            ),
        ];
    }
}
