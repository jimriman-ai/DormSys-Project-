<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Presentation\Http\Responses;

use App\Modules\Reporting\Application\DTOs\ActorActivitySummaryItemDto;
use App\Modules\Reporting\Application\DTOs\AggregateDrillDownReadModel;
use App\Modules\Reporting\Application\DTOs\AuditWindowBucketDto;
use App\Modules\Reporting\Application\DTOs\AuditWindowSummaryReadModel;
use App\Modules\Reporting\Application\DTOs\ComplianceExportReadModel;
use App\Modules\Reporting\Application\DTOs\CorrelationAuditBundleReadModel;
use App\Modules\Reporting\Application\DTOs\EntityAuditTimelineReadModel;
use App\Modules\Reporting\Application\DTOs\EntityTimelineSummaryDto;
use App\Modules\Reporting\Application\DTOs\ReportingProvenanceDto;
use App\Modules\Reporting\Application\DTOs\ReportingTimelineItemDto;
use App\Modules\Reporting\Application\DTOs\SecurityAuditEventReadModel;
use DateTimeImmutable;

final class ReportingReadModelSerializer
{
    /**
     * @return array{0: array<string, mixed>, 1: array<string, mixed>}
     */
    public static function split(object $readModel): array
    {
        return match (true) {
            $readModel instanceof EntityAuditTimelineReadModel => self::splitEntityTimeline($readModel),
            $readModel instanceof CorrelationAuditBundleReadModel => self::splitCorrelationBundle($readModel),
            $readModel instanceof AuditWindowSummaryReadModel => self::splitWindowSummary($readModel),
            $readModel instanceof ComplianceExportReadModel => self::splitComplianceExport($readModel),
            $readModel instanceof SecurityAuditEventReadModel => self::splitSecurityActorActivity($readModel),
            $readModel instanceof AggregateDrillDownReadModel => self::splitDrillDown($readModel),
            default => throw new \InvalidArgumentException('Unsupported reporting read model.'),
        };
    }

    /**
     * @return array<string, mixed>
     */
    public static function provenance(ReportingProvenanceDto $provenance): array
    {
        return [
            'sourceTier' => $provenance->sourceTier,
            'refreshedAt' => self::formatDateTime($provenance->refreshedAt),
            'projectionVersion' => $provenance->projectionVersion,
            'includeArchived' => $provenance->includeArchived,
            'filterHash' => $provenance->filterHash,
        ];
    }

    /**
     * @return array{0: array<string, mixed>, 1: array<string, mixed>}
     */
    private static function splitEntityTimeline(EntityAuditTimelineReadModel $readModel): array
    {
        return [
            [
                'items' => array_map(self::timelineItem(...), $readModel->items),
                'total' => $readModel->total,
                'page' => $readModel->page,
                'perPage' => $readModel->perPage,
                'lastPage' => $readModel->lastPage,
                'eventTypeHistogram' => $readModel->eventTypeHistogram,
                'summary' => self::summary($readModel->summary),
            ],
            self::provenance($readModel->provenance),
        ];
    }

    /**
     * @return array{0: array<string, mixed>, 1: array<string, mixed>}
     */
    private static function splitCorrelationBundle(CorrelationAuditBundleReadModel $readModel): array
    {
        return [
            [
                'correlationId' => $readModel->correlationId,
                'items' => array_map(self::timelineItem(...), $readModel->items),
                'itemCount' => $readModel->itemCount,
                'occurredAtMin' => self::formatDateTime($readModel->occurredAtMin),
                'occurredAtMax' => self::formatDateTime($readModel->occurredAtMax),
                'distinctEntityIds' => $readModel->distinctEntityIds,
                'distinctActorIds' => $readModel->distinctActorIds,
                'eventTypeHistogram' => $readModel->eventTypeHistogram,
            ],
            self::provenance($readModel->provenance),
        ];
    }

    /**
     * @return array{0: array<string, mixed>, 1: array<string, mixed>}
     */
    private static function splitWindowSummary(AuditWindowSummaryReadModel $readModel): array
    {
        return [
            [
                'buckets' => array_map(self::bucket(...), $readModel->buckets),
            ],
            self::provenance($readModel->provenance),
        ];
    }

    /**
     * @return array{0: array<string, mixed>, 1: array<string, mixed>}
     */
    private static function splitComplianceExport(ComplianceExportReadModel $readModel): array
    {
        return [
            [
                'snapshotId' => $readModel->snapshotId,
                'generatedAt' => self::formatDateTime($readModel->generatedAt),
                'filterManifest' => $readModel->filterManifest,
                'lineItemSourceAuditLogIds' => $readModel->lineItemSourceAuditLogIds,
                'lineItems' => array_map(self::timelineItem(...), $readModel->lineItems),
                'summaryBuckets' => array_map(self::bucket(...), $readModel->summaryBuckets),
                'total' => $readModel->total,
                'page' => $readModel->page,
                'perPage' => $readModel->perPage,
                'lastPage' => $readModel->lastPage,
            ],
            self::provenance($readModel->provenance),
        ];
    }

    /**
     * @return array{0: array<string, mixed>, 1: array<string, mixed>}
     */
    private static function splitSecurityActorActivity(SecurityAuditEventReadModel $readModel): array
    {
        return [
            [
                'summaries' => array_map(self::actorSummary(...), $readModel->summaries),
            ],
            self::provenance($readModel->provenance),
        ];
    }

    /**
     * @return array{0: array<string, mixed>, 1: array<string, mixed>}
     */
    private static function splitDrillDown(AggregateDrillDownReadModel $readModel): array
    {
        return [
            [
                'items' => array_map(self::timelineItem(...), $readModel->items),
                'total' => $readModel->total,
                'page' => $readModel->page,
                'perPage' => $readModel->perPage,
                'lastPage' => $readModel->lastPage,
                'summary' => self::summary($readModel->summary),
            ],
            self::provenance($readModel->provenance),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function timelineItem(ReportingTimelineItemDto $item): array
    {
        return [
            'auditLogId' => $item->auditLogId,
            'correlationId' => $item->correlationId,
            'eventType' => $item->eventType,
            'entityType' => $item->entityType,
            'entityId' => $item->entityId,
            'actorType' => $item->actorType,
            'actorId' => $item->actorId,
            'sourceContext' => $item->sourceContext,
            'occurredAt' => self::formatDateTime($item->occurredAt),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function summary(EntityTimelineSummaryDto $summary): array
    {
        return [
            'totalCount' => $summary->totalCount,
            'pageItemCount' => $summary->pageItemCount,
            'firstOccurredAt' => self::formatDateTime($summary->firstOccurredAt),
            'lastOccurredAt' => self::formatDateTime($summary->lastOccurredAt),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function bucket(AuditWindowBucketDto $bucket): array
    {
        return [
            'windowStart' => self::formatDateTime($bucket->windowStart),
            'windowEnd' => self::formatDateTime($bucket->windowEnd),
            'eventType' => $bucket->eventType,
            'sourceContext' => $bucket->sourceContext,
            'actorType' => $bucket->actorType,
            'entityType' => $bucket->entityType,
            'eventCount' => $bucket->eventCount,
            'distinctEntityCount' => $bucket->distinctEntityCount,
            'distinctActorCount' => $bucket->distinctActorCount,
            'topEventTypes' => $bucket->topEventTypes,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function actorSummary(ActorActivitySummaryItemDto $summary): array
    {
        return [
            'actorType' => $summary->actorType,
            'actorId' => $summary->actorId,
            'windowStart' => self::formatDateTime($summary->windowStart),
            'windowEnd' => self::formatDateTime($summary->windowEnd),
            'eventCount' => $summary->eventCount,
            'distinctEventTypes' => $summary->distinctEventTypes,
            'distinctEntitiesTouched' => $summary->distinctEntitiesTouched,
        ];
    }

    private static function formatDateTime(?DateTimeImmutable $value): ?string
    {
        return $value?->format(DateTimeImmutable::ATOM);
    }
}
