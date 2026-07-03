<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Application\Contracts;

use App\Modules\Reporting\Application\DTOs\ActorTimelineQuery;
use App\Modules\Reporting\Application\DTOs\AggregateDrillDownQuery;
use App\Modules\Reporting\Application\DTOs\AggregateDrillDownReadModel;
use App\Modules\Reporting\Application\DTOs\EntityAuditTimelineReadModel;
use App\Modules\Reporting\Application\DTOs\EntityTimelineQuery;

interface ReportingReadContract
{
    public function entityTimeline(EntityTimelineQuery $query): EntityAuditTimelineReadModel;

    public function actorTimeline(ActorTimelineQuery $query): EntityAuditTimelineReadModel;

    public function drillDown(AggregateDrillDownQuery $query): AggregateDrillDownReadModel;
}
