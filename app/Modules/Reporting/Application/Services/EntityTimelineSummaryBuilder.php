<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Application\Services;

use App\Modules\Reporting\Application\DTOs\EntityTimelineSummaryDto;
use App\Modules\Reporting\Application\DTOs\ReportingTimelineItemDto;

final class EntityTimelineSummaryBuilder
{
    /**
     * @param  list<ReportingTimelineItemDto>  $items
     */
    public function build(array $items, int $totalCount): EntityTimelineSummaryDto
    {
        if ($items === []) {
            return new EntityTimelineSummaryDto(
                totalCount: $totalCount,
                pageItemCount: 0,
                firstOccurredAt: null,
                lastOccurredAt: null,
            );
        }

        $occurredAtValues = array_map(
            static fn (ReportingTimelineItemDto $item) => $item->occurredAt,
            $items,
        );

        return new EntityTimelineSummaryDto(
            totalCount: $totalCount,
            pageItemCount: count($items),
            firstOccurredAt: min($occurredAtValues),
            lastOccurredAt: max($occurredAtValues),
        );
    }
}
