<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Application\Services;

use App\Modules\Reporting\Application\Contracts\Ports\CorrelationProjectionQueryPort;
use App\Modules\Reporting\Application\Contracts\Ports\ProjectionCursorControlPort;
use App\Modules\Reporting\Application\DTOs\CorrelationAuditBundleReadModel;
use App\Modules\Reporting\Application\DTOs\CorrelationBundleQuery;
use App\Modules\Reporting\Application\DTOs\CorrelationProjectionEntryReadDto;
use App\Modules\Reporting\Application\DTOs\ReportingTimelineItemDto;
use App\Modules\Reporting\Domain\Enums\ArchiveVisibilityTier;
use App\Modules\Reporting\Domain\Enums\ProjectionFamily;
use App\Modules\Reporting\Domain\Enums\RefreshMode;

final class QueryCorrelationBundleAction
{
    public function __construct(
        private readonly CorrelationProjectionQueryPort $correlationQuery,
        private readonly ProjectionCursorControlPort $cursorControl,
        private readonly ReportingProvenanceFactory $provenanceFactory,
    ) {}

    public function execute(CorrelationBundleQuery $query): CorrelationAuditBundleReadModel
    {
        $entries = $this->correlationQuery->findBundleEntries($query);
        $items = array_map(
            static fn (CorrelationProjectionEntryReadDto $entry): ReportingTimelineItemDto => ReportingTimelineItemDto::fromCorrelationProjection(
                sourceAuditLogId: $entry->sourceAuditLogId,
                correlationId: $entry->correlationId,
                eventType: $entry->eventType,
                entityType: $entry->entityType,
                entityId: $entry->entityId,
                actorType: $entry->actorType,
                actorId: $entry->actorId,
                sourceContext: $entry->sourceContext,
                occurredAt: $entry->occurredAt,
            ),
            $entries,
        );

        $histogram = [];
        $entityIds = [];
        $actorIds = [];
        $occurredAtMin = null;
        $occurredAtMax = null;
        $latestIngested = null;

        foreach ($entries as $entry) {
            $histogram[$entry->eventType] = ($histogram[$entry->eventType] ?? 0) + 1;
            $entityIds[$entry->entityId] = true;
            $actorIds[$entry->actorId] = true;

            if ($occurredAtMin === null || $entry->occurredAt < $occurredAtMin) {
                $occurredAtMin = $entry->occurredAt;
            }

            if ($occurredAtMax === null || $entry->occurredAt > $occurredAtMax) {
                $occurredAtMax = $entry->occurredAt;
            }

            if ($latestIngested === null || $entry->ingestedAt > $latestIngested) {
                $latestIngested = $entry->ingestedAt;
            }
        }

        $tier = $query->includeArchived
            ? ArchiveVisibilityTier::IncludeArchived
            : ArchiveVisibilityTier::ActiveOnly;

        $cursor = $this->cursorControl->resolveCursor(
            ProjectionFamily::Correlation,
            $tier,
            '1.0.0',
            RefreshMode::Incremental,
        );

        $provenance = $this->provenanceFactory->forT1(
            [
                'correlationId' => $query->correlationId,
                'eventTypes' => $query->eventTypes,
            ],
            $query->includeArchived,
            $latestIngested ?? $cursor->refreshedAt,
            $cursor->projectionVersion,
        );

        return new CorrelationAuditBundleReadModel(
            correlationId: $query->correlationId,
            items: $items,
            itemCount: count($items),
            occurredAtMin: $occurredAtMin,
            occurredAtMax: $occurredAtMax,
            distinctEntityIds: array_keys($entityIds),
            distinctActorIds: array_keys($actorIds),
            eventTypeHistogram: $histogram,
            provenance: $provenance,
        );
    }
}
