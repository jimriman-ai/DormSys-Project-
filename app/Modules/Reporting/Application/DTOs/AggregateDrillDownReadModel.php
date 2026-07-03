<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Application\DTOs;

final readonly class AggregateDrillDownReadModel
{
    /**
     * @param  list<ReportingTimelineItemDto>  $items
     */
    public function __construct(
        public array $items,
        public int $total,
        public int $page,
        public int $perPage,
        public int $lastPage,
        public ReportingProvenanceDto $provenance,
        public EntityTimelineSummaryDto $summary,
    ) {}
}
