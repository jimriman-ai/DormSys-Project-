<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Application\Contracts;

use App\Modules\Reporting\Application\DTOs\ActorTimelineQuery;
use App\Modules\Reporting\Application\DTOs\AggregateDrillDownQuery;
use App\Modules\Reporting\Application\DTOs\AggregateDrillDownReadModel;
use App\Modules\Reporting\Application\DTOs\AuditWindowSummaryQuery;
use App\Modules\Reporting\Application\DTOs\AuditWindowSummaryReadModel;
use App\Modules\Reporting\Application\DTOs\CorrelationAuditBundleReadModel;
use App\Modules\Reporting\Application\DTOs\CorrelationBundleQuery;
use App\Modules\Reporting\Application\DTOs\EntityAuditTimelineReadModel;
use App\Modules\Reporting\Application\DTOs\EntityTimelineQuery;
use App\Modules\Reporting\Application\DTOs\SecurityActorActivityQuery;
use App\Modules\Reporting\Application\DTOs\SecurityAuditEventReadModel;

interface ReportingReadContract
{
    public function entityTimeline(EntityTimelineQuery $query): EntityAuditTimelineReadModel;

    public function actorTimeline(ActorTimelineQuery $query): EntityAuditTimelineReadModel;

    public function drillDown(AggregateDrillDownQuery $query): AggregateDrillDownReadModel;

    public function correlationBundle(CorrelationBundleQuery $query): CorrelationAuditBundleReadModel;

    public function auditWindowSummary(AuditWindowSummaryQuery $query): AuditWindowSummaryReadModel;

    public function securityActorActivity(SecurityActorActivityQuery $query): SecurityAuditEventReadModel;
}
