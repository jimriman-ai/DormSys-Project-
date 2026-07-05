<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Application\Services;

use App\Modules\Audit\Application\Services\AuditReadPolicyEnforcementPoint;
use App\Modules\Reporting\Application\Contracts\Ports\AggregateDrillDownPort;
use App\Modules\Reporting\Application\Contracts\ReportingReadContract;
use App\Modules\Reporting\Application\DTOs\ActorTimelineQuery;
use App\Modules\Reporting\Application\DTOs\AggregateDrillDownQuery;
use App\Modules\Reporting\Application\DTOs\AggregateDrillDownReadModel;
use App\Modules\Reporting\Application\DTOs\AuditWindowSummaryQuery;
use App\Modules\Reporting\Application\DTOs\AuditWindowSummaryReadModel;
use App\Modules\Reporting\Application\DTOs\ComplianceExportQuery;
use App\Modules\Reporting\Application\DTOs\ComplianceExportReadModel;
use App\Modules\Reporting\Application\DTOs\CorrelationAuditBundleReadModel;
use App\Modules\Reporting\Application\DTOs\CorrelationBundleQuery;
use App\Modules\Reporting\Application\DTOs\EntityAuditTimelineReadModel;
use App\Modules\Reporting\Application\DTOs\EntityTimelineQuery;
use App\Modules\Reporting\Application\DTOs\SecurityActorActivityQuery;
use App\Modules\Reporting\Application\DTOs\SecurityAuditEventReadModel;

final class ReportingReadService implements ReportingReadContract
{
    public function __construct(
        private readonly AuditReadPolicyEnforcementPoint $auditReadPolicy,
        private readonly ReportingArchiveVisibilityGuard $archiveVisibility,
        private readonly QueryEntityAuditTimelineAction $queryEntityAuditTimeline,
        private readonly QueryActorAuditTimelineAction $queryActorAuditTimeline,
        private readonly QueryCorrelationBundleAction $queryCorrelationBundle,
        private readonly QueryAuditWindowSummaryAction $queryAuditWindowSummary,
        private readonly QuerySecurityActorActivityAction $querySecurityActorActivity,
        private readonly QueryComplianceExportAction $queryComplianceExport,
        private readonly AggregateDrillDownPort $aggregateDrillDown,
    ) {}

    public function entityTimeline(EntityTimelineQuery $query): EntityAuditTimelineReadModel
    {
        $this->enforceSensitiveReadAccess();

        $includeArchived = $this->archiveVisibility->resolveIncludeArchived($query->includeArchived);

        return $this->queryEntityAuditTimeline->execute($this->withIncludeArchived($query, $includeArchived));
    }

    public function actorTimeline(ActorTimelineQuery $query): EntityAuditTimelineReadModel
    {
        $this->enforceSensitiveReadAccess();

        $includeArchived = $this->archiveVisibility->resolveIncludeArchived($query->includeArchived);

        return $this->queryActorAuditTimeline->execute($this->withIncludeArchivedForActor($query, $includeArchived));
    }

    public function drillDown(AggregateDrillDownQuery $query): AggregateDrillDownReadModel
    {
        $this->enforceSensitiveReadAccess();

        $includeArchived = $this->archiveVisibility->resolveIncludeArchived($query->includeArchived);

        return $this->aggregateDrillDown->drillDown($this->withIncludeArchivedForDrillDown($query, $includeArchived));
    }

    public function correlationBundle(CorrelationBundleQuery $query): CorrelationAuditBundleReadModel
    {
        $this->enforceSensitiveReadAccess();

        $includeArchived = $this->archiveVisibility->resolveIncludeArchived($query->includeArchived);

        return $this->queryCorrelationBundle->execute($this->withIncludeArchivedForCorrelation($query, $includeArchived));
    }

    public function auditWindowSummary(AuditWindowSummaryQuery $query): AuditWindowSummaryReadModel
    {
        $this->enforceSensitiveReadAccess();

        $includeArchived = $this->archiveVisibility->resolveIncludeArchived($query->includeArchived);

        return $this->queryAuditWindowSummary->execute($this->withIncludeArchivedForWindow($query, $includeArchived));
    }

    public function securityActorActivity(SecurityActorActivityQuery $query): SecurityAuditEventReadModel
    {
        $this->enforceSensitiveReadAccess();

        $includeArchived = $this->archiveVisibility->resolveIncludeArchived($query->includeArchived);

        return $this->querySecurityActorActivity->execute($this->withIncludeArchivedForSecurityActor($query, $includeArchived));
    }

    public function complianceExport(ComplianceExportQuery $query): ComplianceExportReadModel
    {
        $this->enforceSensitiveReadAccess();

        $includeArchived = $this->archiveVisibility->resolveIncludeArchived($query->includeArchived);

        return $this->queryComplianceExport->execute($this->withIncludeArchivedForComplianceExport($query, $includeArchived));
    }

    private function enforceSensitiveReadAccess(): void
    {
        $this->auditReadPolicy->enforce();
    }

    private function withIncludeArchivedForComplianceExport(ComplianceExportQuery $query, bool $includeArchived): ComplianceExportQuery
    {
        if ($query->includeArchived === $includeArchived) {
            return $query;
        }

        return new ComplianceExportQuery(
            windowStart: $query->windowStart,
            windowEnd: $query->windowEnd,
            granularity: $query->granularity,
            eventTypes: $query->eventTypes,
            eventType: $query->eventType,
            sourceContext: $query->sourceContext,
            actorType: $query->actorType,
            entityType: $query->entityType,
            includeArchived: $includeArchived,
            page: $query->page,
            perPage: $query->perPage,
        );
    }

    private function withIncludeArchivedForCorrelation(CorrelationBundleQuery $query, bool $includeArchived): CorrelationBundleQuery
    {
        if ($query->includeArchived === $includeArchived) {
            return $query;
        }

        return new CorrelationBundleQuery(
            correlationId: $query->correlationId,
            includeArchived: $includeArchived,
            eventTypes: $query->eventTypes,
        );
    }

    private function withIncludeArchivedForWindow(AuditWindowSummaryQuery $query, bool $includeArchived): AuditWindowSummaryQuery
    {
        if ($query->includeArchived === $includeArchived) {
            return $query;
        }

        return new AuditWindowSummaryQuery(
            windowStart: $query->windowStart,
            windowEnd: $query->windowEnd,
            granularity: $query->granularity,
            eventType: $query->eventType,
            sourceContext: $query->sourceContext,
            actorType: $query->actorType,
            entityType: $query->entityType,
            includeArchived: $includeArchived,
        );
    }

    private function withIncludeArchivedForSecurityActor(SecurityActorActivityQuery $query, bool $includeArchived): SecurityActorActivityQuery
    {
        if ($query->includeArchived === $includeArchived) {
            return $query;
        }

        return new SecurityActorActivityQuery(
            actorType: $query->actorType,
            actorId: $query->actorId,
            windowStart: $query->windowStart,
            windowEnd: $query->windowEnd,
            granularity: $query->granularity,
            includeArchived: $includeArchived,
        );
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

    private function withIncludeArchivedForActor(ActorTimelineQuery $query, bool $includeArchived): ActorTimelineQuery
    {
        if ($query->includeArchived === $includeArchived) {
            return $query;
        }

        return new ActorTimelineQuery(
            actorType: $query->actorType,
            actorId: $query->actorId,
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
