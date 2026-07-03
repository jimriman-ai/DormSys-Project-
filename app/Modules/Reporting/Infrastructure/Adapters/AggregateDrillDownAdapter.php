<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Infrastructure\Adapters;

use App\Modules\Reporting\Application\Contracts\Ports\AggregateDrillDownPort;
use App\Modules\Reporting\Application\DTOs\AggregateDrillDownQuery;
use App\Modules\Reporting\Application\DTOs\AggregateDrillDownReadModel;
use App\Modules\Reporting\Application\DTOs\EntityTimelineQuery;
use App\Modules\Reporting\Application\Services\QueryEntityAuditTimelineAction;

final class AggregateDrillDownAdapter implements AggregateDrillDownPort
{
    public function __construct(
        private readonly QueryEntityAuditTimelineAction $timelineAction,
    ) {}

    public function drillDown(AggregateDrillDownQuery $query): AggregateDrillDownReadModel
    {
        $timeline = $this->timelineAction->execute(new EntityTimelineQuery(
            entityType: $query->entityType,
            entityId: $query->entityId,
            eventTypes: $query->eventTypes,
            occurredFrom: $query->occurredFrom,
            occurredTo: $query->occurredTo,
            includeArchived: $query->includeArchived,
            page: $query->page,
            perPage: $query->perPage,
        ));

        return new AggregateDrillDownReadModel(
            items: $timeline->items,
            total: $timeline->total,
            page: $timeline->page,
            perPage: $timeline->perPage,
            lastPage: $timeline->lastPage,
            provenance: $timeline->provenance,
            summary: $timeline->summary,
        );
    }
}
