<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Application\DTOs;

use DateTimeImmutable;

final readonly class CorrelationAuditBundleReadModel
{
    /**
     * @param  list<ReportingTimelineItemDto>  $items
     * @param  list<string>  $distinctEntityIds
     * @param  list<string>  $distinctActorIds
     * @param  array<string, int>  $eventTypeHistogram
     */
    public function __construct(
        public string $correlationId,
        public array $items,
        public int $itemCount,
        public ?DateTimeImmutable $occurredAtMin,
        public ?DateTimeImmutable $occurredAtMax,
        public array $distinctEntityIds,
        public array $distinctActorIds,
        public array $eventTypeHistogram,
        public ReportingProvenanceDto $provenance,
    ) {}
}
