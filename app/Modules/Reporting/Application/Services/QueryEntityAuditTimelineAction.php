<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Application\Services;

use App\Modules\Reporting\Application\Contracts\Ports\AuditHistorySourceReadPort;
use App\Modules\Reporting\Application\DTOs\EntityAuditTimelineReadModel;
use App\Modules\Reporting\Application\DTOs\EntityTimelineQuery;
use App\Modules\Reporting\Application\DTOs\ReportingTimelineItemDto;
use DateTimeInterface;

final class QueryEntityAuditTimelineAction
{
    public function __construct(
        private readonly AuditHistorySourceReadPort $auditHistorySource,
        private readonly ReportingProvenanceFactory $provenanceFactory,
        private readonly EntityTimelineSummaryBuilder $summaryBuilder,
    ) {}

    public function execute(EntityTimelineQuery $query): EntityAuditTimelineReadModel
    {
        $result = $this->auditHistorySource->queryByEntity(
            entityType: $query->entityType,
            entityId: $query->entityId,
            includeArchived: $query->includeArchived,
            eventTypes: $query->eventTypes,
            occurredFrom: $query->occurredFrom,
            occurredTo: $query->occurredTo,
            page: $query->page,
            perPage: $query->perPage,
        );

        $items = array_map(
            static fn ($item) => ReportingTimelineItemDto::fromAuditHistoryItem($item),
            $result->items,
        );

        $histogram = [];
        foreach ($items as $item) {
            $histogram[$item->eventType] = ($histogram[$item->eventType] ?? 0) + 1;
        }

        $provenance = $this->provenanceFactory->forT0(
            $this->normalizedFilters($query),
            $query->includeArchived,
        );

        return new EntityAuditTimelineReadModel(
            items: $items,
            total: $result->total,
            page: $result->page,
            perPage: $result->perPage,
            lastPage: $result->lastPage,
            provenance: $provenance,
            eventTypeHistogram: $histogram,
            summary: $this->summaryBuilder->build($items, $result->total),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizedFilters(EntityTimelineQuery $query): array
    {
        return [
            'entityType' => $query->entityType,
            'entityId' => $query->entityId,
            'eventTypes' => $query->eventTypes,
            'occurredFrom' => $query->occurredFrom?->format(DateTimeInterface::ATOM),
            'occurredTo' => $query->occurredTo?->format(DateTimeInterface::ATOM),
            'page' => $query->page,
            'perPage' => $query->perPage,
        ];
    }
}
