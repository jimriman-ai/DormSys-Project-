<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Infrastructure\Providers;

use App\Modules\Reporting\Application\Contracts\Ports\ActorActivityQueryPort;
use App\Modules\Reporting\Application\Contracts\Ports\AggregateDrillDownPort;
use App\Modules\Reporting\Application\Contracts\Ports\AuditHistorySourceReadPort;
use App\Modules\Reporting\Application\Contracts\Ports\CorrelationProjectionQueryPort;
use App\Modules\Reporting\Application\Contracts\Ports\ProjectionCursorControlPort;
use App\Modules\Reporting\Application\Contracts\Ports\ProjectionRefreshInputPort;
use App\Modules\Reporting\Application\Contracts\Ports\ProjectionRefreshRunnerPort;
use App\Modules\Reporting\Application\Contracts\Ports\ReportingArchiveVisibilityPort;
use App\Modules\Reporting\Application\Contracts\Ports\WindowAggregateQueryPort;
use App\Modules\Reporting\Application\Contracts\ReportingReadContract;
use App\Modules\Reporting\Application\Services\EntityTimelineSummaryBuilder;
use App\Modules\Reporting\Application\Services\Materializers\ActorActivitySummaryMaterializer;
use App\Modules\Reporting\Application\Services\Materializers\AuditWindowAggregateMaterializer;
use App\Modules\Reporting\Application\Services\Materializers\CorrelationProjectionMaterializer;
use App\Modules\Reporting\Application\Services\ProjectionCursorPositionSelector;
use App\Modules\Reporting\Application\Services\ProjectionDayWindowResolver;
use App\Modules\Reporting\Application\Services\ProjectionRefreshInputService;
use App\Modules\Reporting\Application\Services\ProjectionRefreshMaterializerRegistry;
use App\Modules\Reporting\Application\Services\ProjectionRefreshRunnerService;
use App\Modules\Reporting\Application\Services\QueryActorAuditTimelineAction;
use App\Modules\Reporting\Application\Services\QueryAuditWindowSummaryAction;
use App\Modules\Reporting\Application\Services\QueryCorrelationBundleAction;
use App\Modules\Reporting\Application\Services\QueryEntityAuditTimelineAction;
use App\Modules\Reporting\Application\Services\QuerySecurityActorActivityAction;
use App\Modules\Reporting\Application\Services\ReportingArchiveVisibilityGuard;
use App\Modules\Reporting\Application\Services\ReportingProjectionEventTypeCatalog;
use App\Modules\Reporting\Application\Services\ReportingProvenanceFactory;
use App\Modules\Reporting\Application\Services\ReportingReadService;
use App\Modules\Reporting\Infrastructure\Adapters\ActorActivityQueryAdapter;
use App\Modules\Reporting\Infrastructure\Adapters\AggregateDrillDownAdapter;
use App\Modules\Reporting\Infrastructure\Adapters\AuditHistorySourceReadAdapter;
use App\Modules\Reporting\Infrastructure\Adapters\CorrelationProjectionQueryAdapter;
use App\Modules\Reporting\Infrastructure\Adapters\ProjectionCursorControlAdapter;
use App\Modules\Reporting\Infrastructure\Adapters\ReportingArchiveVisibilityAdapter;
use App\Modules\Reporting\Infrastructure\Adapters\WindowAggregateQueryAdapter;
use App\Modules\Reporting\Infrastructure\Repositories\ActorActivitySummaryRepository;
use App\Modules\Reporting\Infrastructure\Repositories\AuditWindowAggregateRepository;
use App\Modules\Reporting\Infrastructure\Repositories\CorrelationProjectionEntryRepository;
use App\Modules\Reporting\Infrastructure\Repositories\ProjectionCursorRepository;
use App\Modules\Reporting\Infrastructure\Repositories\ProjectionIngestReceiptRepository;
use Illuminate\Support\ServiceProvider;

class ReportingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ReportingProvenanceFactory::class);
        $this->app->singleton(EntityTimelineSummaryBuilder::class);
        $this->app->singleton(ReportingProjectionEventTypeCatalog::class);
        $this->app->singleton(ProjectionCursorRepository::class);
        $this->app->singleton(CorrelationProjectionEntryRepository::class);
        $this->app->singleton(ProjectionIngestReceiptRepository::class);
        $this->app->singleton(AuditWindowAggregateRepository::class);
        $this->app->singleton(ActorActivitySummaryRepository::class);
        $this->app->singleton(ProjectionDayWindowResolver::class);
        $this->app->singleton(ProjectionCursorPositionSelector::class);
        $this->app->singleton(CorrelationProjectionMaterializer::class);
        $this->app->singleton(AuditWindowAggregateMaterializer::class);
        $this->app->singleton(ActorActivitySummaryMaterializer::class);
        $this->app->tag([
            CorrelationProjectionMaterializer::class,
            AuditWindowAggregateMaterializer::class,
            ActorActivitySummaryMaterializer::class,
        ], 'reporting.projection_materializers');
        $this->app->singleton(ProjectionRefreshMaterializerRegistry::class, function ($app): ProjectionRefreshMaterializerRegistry {
            return new ProjectionRefreshMaterializerRegistry(
                $app->tagged('reporting.projection_materializers'),
            );
        });
        $this->app->singleton(ProjectionRefreshRunnerPort::class, ProjectionRefreshRunnerService::class);
        $this->app->singleton(ReportingArchiveVisibilityPort::class, ReportingArchiveVisibilityAdapter::class);
        $this->app->singleton(ReportingArchiveVisibilityGuard::class);
        $this->app->singleton(AuditHistorySourceReadPort::class, AuditHistorySourceReadAdapter::class);
        $this->app->singleton(ProjectionCursorControlPort::class, ProjectionCursorControlAdapter::class);
        $this->app->singleton(ProjectionRefreshInputPort::class, ProjectionRefreshInputService::class);
        $this->app->singleton(QueryEntityAuditTimelineAction::class);
        $this->app->singleton(QueryActorAuditTimelineAction::class);
        $this->app->singleton(QueryCorrelationBundleAction::class);
        $this->app->singleton(QueryAuditWindowSummaryAction::class);
        $this->app->singleton(QuerySecurityActorActivityAction::class);
        $this->app->singleton(CorrelationProjectionQueryPort::class, CorrelationProjectionQueryAdapter::class);
        $this->app->singleton(WindowAggregateQueryPort::class, WindowAggregateQueryAdapter::class);
        $this->app->singleton(ActorActivityQueryPort::class, ActorActivityQueryAdapter::class);
        $this->app->singleton(AggregateDrillDownPort::class, AggregateDrillDownAdapter::class);
        $this->app->singleton(ReportingReadService::class);
        $this->app->singleton(ReportingReadContract::class, ReportingReadService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(database_path('migrations/modules/reporting'));
    }
}
