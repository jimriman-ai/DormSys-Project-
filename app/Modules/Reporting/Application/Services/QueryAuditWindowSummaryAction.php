<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Application\Services;

use App\Modules\Reporting\Application\Contracts\Ports\WindowAggregateQueryPort;
use App\Modules\Reporting\Application\DTOs\AuditWindowBucketDto;
use App\Modules\Reporting\Application\DTOs\AuditWindowSummaryQuery;
use App\Modules\Reporting\Application\DTOs\AuditWindowSummaryReadModel;
use DateTimeInterface;

final class QueryAuditWindowSummaryAction
{
    public function __construct(
        private readonly WindowAggregateQueryPort $windowQuery,
        private readonly ReportingProvenanceFactory $provenanceFactory,
    ) {}

    public function execute(AuditWindowSummaryQuery $query): AuditWindowSummaryReadModel
    {
        $aggregates = $this->windowQuery->findBuckets($query);

        $buckets = array_map(
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
            $aggregates,
        );

        $refreshedAt = null;
        $projectionVersion = null;

        foreach ($aggregates as $aggregate) {
            if ($refreshedAt === null || $aggregate->refreshedAt > $refreshedAt) {
                $refreshedAt = $aggregate->refreshedAt;
            }

            $projectionVersion ??= $aggregate->projectionVersion;
        }

        $provenance = $this->provenanceFactory->forT1(
            $this->normalizedFilters($query),
            $query->includeArchived,
            $refreshedAt,
            $projectionVersion,
        );

        return new AuditWindowSummaryReadModel(
            buckets: $buckets,
            provenance: $provenance,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizedFilters(AuditWindowSummaryQuery $query): array
    {
        return [
            'windowStart' => $query->windowStart->format(DateTimeInterface::ATOM),
            'windowEnd' => $query->windowEnd->format(DateTimeInterface::ATOM),
            'granularity' => $query->granularity->value,
            'eventType' => $query->eventType,
            'sourceContext' => $query->sourceContext,
            'actorType' => $query->actorType,
            'entityType' => $query->entityType,
        ];
    }
}
