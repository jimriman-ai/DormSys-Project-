<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Application\Services;

use App\Modules\Audit\Application\DTOs\AuditHistoryItemDto;
use App\Modules\Reporting\Application\Contracts\Ports\AuditHistorySourceReadPort;
use App\Modules\Reporting\Application\Contracts\Ports\ExportSnapshotAssemblyPort;
use App\Modules\Reporting\Application\Contracts\Ports\WindowAggregateQueryPort;
use App\Modules\Reporting\Application\DTOs\AuditWindowBucketDto;
use App\Modules\Reporting\Application\DTOs\AuditWindowSummaryQuery;
use App\Modules\Reporting\Application\DTOs\ComplianceExportQuery;
use App\Modules\Reporting\Application\DTOs\ComplianceExportReadModel;
use App\Modules\Reporting\Application\DTOs\ReportingTimelineItemDto;

final class QueryComplianceExportAction
{
    public function __construct(
        private readonly ExportSnapshotAssemblyPort $exportAssembly,
        private readonly AuditHistorySourceReadPort $auditHistorySource,
        private readonly WindowAggregateQueryPort $windowAggregates,
        private readonly ReportingProvenanceFactory $provenanceFactory,
    ) {}

    public function execute(ComplianceExportQuery $query): ComplianceExportReadModel
    {
        $manifest = $this->exportAssembly->assembleManifest($query);
        $t0ItemsById = $this->resolveT0LineItems($query, $manifest->lineItemSourceAuditLogIds);

        $total = count($manifest->lineItemSourceAuditLogIds);
        $lastPage = max(1, (int) ceil($total / $query->perPage));
        $pageOffset = ($query->page - 1) * $query->perPage;
        $pageIds = array_slice($manifest->lineItemSourceAuditLogIds, $pageOffset, $query->perPage);

        $lineItems = [];
        foreach ($pageIds as $auditLogId) {
            if (! isset($t0ItemsById[$auditLogId])) {
                continue;
            }

            $lineItems[] = ReportingTimelineItemDto::fromAuditHistoryItem($t0ItemsById[$auditLogId]);
        }

        $summaryBuckets = array_map(
            static fn ($aggregate) => new AuditWindowBucketDto(
                windowStart: $aggregate->windowStart,
                windowEnd: $aggregate->windowEnd,
                eventType: $aggregate->eventType,
                sourceContext: $aggregate->sourceContext,
                actorType: $aggregate->actorType,
                entityType: $aggregate->entityType,
                eventCount: $aggregate->eventCount,
                distinctEntityCount: $aggregate->distinctEntityCount,
                distinctActorCount: $aggregate->distinctActorCount,
                topEventTypes: $aggregate->topEventTypes,
            ),
            $this->windowAggregates->findBuckets(new AuditWindowSummaryQuery(
                windowStart: $query->windowStart,
                windowEnd: $query->windowEnd,
                granularity: $query->granularity,
                eventType: $query->eventType,
                sourceContext: $query->sourceContext,
                actorType: $query->actorType,
                entityType: $query->entityType,
                includeArchived: $query->includeArchived,
            )),
        );

        $provenance = $this->provenanceFactory->forMixed(
            $manifest->filterManifest,
            $query->includeArchived,
            $manifest->refreshedAt,
            $manifest->projectionVersion,
        );

        return new ComplianceExportReadModel(
            snapshotId: $manifest->snapshotId,
            generatedAt: $manifest->generatedAt,
            filterManifest: $manifest->filterManifest,
            lineItemSourceAuditLogIds: $manifest->lineItemSourceAuditLogIds,
            lineItems: $lineItems,
            summaryBuckets: $summaryBuckets,
            total: $total,
            page: $query->page,
            perPage: $query->perPage,
            lastPage: $lastPage,
            provenance: $provenance,
        );
    }

    /**
     * @param  list<string>  $manifestAuditLogIds
     * @return array<string, AuditHistoryItemDto>
     */
    private function resolveT0LineItems(ComplianceExportQuery $query, array $manifestAuditLogIds): array
    {
        if ($manifestAuditLogIds === []) {
            return [];
        }

        $manifestSet = array_fill_keys($manifestAuditLogIds, true);
        $resolved = [];
        $page = 1;

        do {
            $pageResult = $this->auditHistorySource->queryInWindow(
                includeArchived: $query->includeArchived,
                eventTypes: $query->eventTypes,
                occurredFrom: $query->windowStart,
                occurredTo: $query->windowEnd,
                page: $page,
                perPage: 200,
            );

            foreach ($pageResult->items as $item) {
                if (isset($manifestSet[$item->auditLogId])) {
                    $resolved[$item->auditLogId] = $item;
                }
            }

            if (count($resolved) === count($manifestAuditLogIds)) {
                break;
            }

            $page++;
        } while ($page <= $pageResult->lastPage);

        return $resolved;
    }
}
