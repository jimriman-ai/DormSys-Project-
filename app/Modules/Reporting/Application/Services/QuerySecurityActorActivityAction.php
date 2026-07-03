<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Application\Services;

use App\Modules\Reporting\Application\Contracts\Ports\ActorActivityQueryPort;
use App\Modules\Reporting\Application\DTOs\ActorActivitySummaryItemDto;
use App\Modules\Reporting\Application\DTOs\SecurityActorActivityQuery;
use App\Modules\Reporting\Application\DTOs\SecurityAuditEventReadModel;
use DateTimeInterface;

final class QuerySecurityActorActivityAction
{
    public function __construct(
        private readonly ActorActivityQueryPort $actorActivityQuery,
        private readonly ReportingProvenanceFactory $provenanceFactory,
    ) {}

    public function execute(SecurityActorActivityQuery $query): SecurityAuditEventReadModel
    {
        $rows = $this->actorActivityQuery->findSummaries($query);

        $summaries = array_map(
            static fn ($row) => new ActorActivitySummaryItemDto(
                actorType: $row->actorType,
                actorId: $row->actorId,
                windowStart: $row->windowStart,
                windowEnd: $row->windowEnd,
                eventCount: $row->eventCount,
                distinctEventTypes: $row->distinctEventTypes,
                distinctEntitiesTouched: $row->distinctEntitiesTouched,
            ),
            $rows,
        );

        $refreshedAt = null;
        $projectionVersion = null;

        foreach ($rows as $row) {
            if ($refreshedAt === null || $row->refreshedAt > $refreshedAt) {
                $refreshedAt = $row->refreshedAt;
            }

            $projectionVersion ??= $row->projectionVersion;
        }

        $provenance = $this->provenanceFactory->forT1(
            $this->normalizedFilters($query),
            $query->includeArchived,
            $refreshedAt,
            $projectionVersion,
        );

        return new SecurityAuditEventReadModel(
            summaries: $summaries,
            provenance: $provenance,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizedFilters(SecurityActorActivityQuery $query): array
    {
        return [
            'actorType' => $query->actorType,
            'actorId' => $query->actorId,
            'windowStart' => $query->windowStart->format(DateTimeInterface::ATOM),
            'windowEnd' => $query->windowEnd->format(DateTimeInterface::ATOM),
            'granularity' => $query->granularity->value,
        ];
    }
}
