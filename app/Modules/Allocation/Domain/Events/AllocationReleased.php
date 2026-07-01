<?php

declare(strict_types=1);

namespace App\Modules\Allocation\Domain\Events;

use App\Modules\Allocation\Domain\Models\Allocation;
use App\Support\Events\BaseEvent;

final class AllocationReleased extends BaseEvent
{
    public const string EVENT_NAME = 'allocation.released';

    public const string VERSION = '1.0';

    public static function forAllocation(Allocation $allocation): self
    {
        return self::raise($allocation->requireId()->value, [
            'person_id' => $allocation->personId->value,
            'bed_id' => $allocation->bedId,
            'release_reason' => $allocation->releaseReason,
            'released_at' => $allocation->releasedAt?->format(DATE_ATOM),
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
                ['allocation_id' => $this->aggregateId],
                $this->payload,
                ['occurred_at' => $this->occurredAt->format(DATE_ATOM)],
            ),
        ];
    }
}
