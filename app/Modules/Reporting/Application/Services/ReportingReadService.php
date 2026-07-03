<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Application\Services;

use App\Modules\Audit\Application\Contracts\AuditAuthorizationPort;
use App\Modules\Reporting\Application\Contracts\Ports\AggregateDrillDownPort;
use App\Modules\Reporting\Application\Contracts\ReportingReadContract;
use App\Modules\Reporting\Application\DTOs\AggregateDrillDownQuery;
use App\Modules\Reporting\Application\DTOs\AggregateDrillDownReadModel;
use App\Modules\Reporting\Application\DTOs\EntityAuditTimelineReadModel;
use App\Modules\Reporting\Application\DTOs\EntityTimelineQuery;

final class ReportingReadService implements ReportingReadContract
{
    public function __construct(
        private readonly AuditAuthorizationPort $authorization,
        private readonly ReportingArchiveVisibilityGuard $archiveVisibility,
        private readonly QueryEntityAuditTimelineAction $queryEntityAuditTimeline,
        private readonly AggregateDrillDownPort $aggregateDrillDown,
    ) {}

    public function entityTimeline(EntityTimelineQuery $query): EntityAuditTimelineReadModel
    {
        $this->authorization->authorizeRead();

        $includeArchived = $this->archiveVisibility->resolveIncludeArchived($query->includeArchived);

        return $this->queryEntityAuditTimeline->execute($this->withIncludeArchived($query, $includeArchived));
    }

    public function drillDown(AggregateDrillDownQuery $query): AggregateDrillDownReadModel
    {
        $this->authorization->authorizeRead();

        $includeArchived = $this->archiveVisibility->resolveIncludeArchived($query->includeArchived);

        return $this->aggregateDrillDown->drillDown($this->withIncludeArchivedForDrillDown($query, $includeArchived));
    }

    private function withIncludeArchived(EntityTimelineQuery $query, bool $includeArchived): EntityTimelineQuery
    {
        if ($query->includeArchived === $includeArchived) {
            return $query;
        }

        return new EntityTimelineQuery(
            entityType: $query->entityType,
            entityId: $query->entityId,
            eventTypes: $query->eventTypes,
            occurredFrom: $query->occurredFrom,
            occurredTo: $query->occurredTo,
            includeArchived: $includeArchived,
            page: $query->page,
            perPage: $query->perPage,
        );
    }

    private function withIncludeArchivedForDrillDown(AggregateDrillDownQuery $query, bool $includeArchived): AggregateDrillDownQuery
    {
        if ($query->includeArchived === $includeArchived) {
            return $query;
        }

        return new AggregateDrillDownQuery(
            entityType: $query->entityType,
            entityId: $query->entityId,
            eventTypes: $query->eventTypes,
            occurredFrom: $query->occurredFrom,
            occurredTo: $query->occurredTo,
            includeArchived: $includeArchived,
            page: $query->page,
            perPage: $query->perPage,
            drillDownHandle: $query->drillDownHandle,
        );
    }
}
