<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Infrastructure\Providers;

use App\Modules\Reporting\Application\Contracts\Ports\AggregateDrillDownPort;
use App\Modules\Reporting\Application\Contracts\Ports\AuditHistorySourceReadPort;
use App\Modules\Reporting\Application\Contracts\Ports\ReportingArchiveVisibilityPort;
use App\Modules\Reporting\Application\Contracts\ReportingReadContract;
use App\Modules\Reporting\Application\Services\EntityTimelineSummaryBuilder;
use App\Modules\Reporting\Application\Services\QueryActorAuditTimelineAction;
use App\Modules\Reporting\Application\Services\QueryEntityAuditTimelineAction;
use App\Modules\Reporting\Application\Services\ReportingArchiveVisibilityGuard;
use App\Modules\Reporting\Application\Services\ReportingProvenanceFactory;
use App\Modules\Reporting\Application\Services\ReportingReadService;
use App\Modules\Reporting\Infrastructure\Adapters\AggregateDrillDownAdapter;
use App\Modules\Reporting\Infrastructure\Adapters\AuditHistorySourceReadAdapter;
use App\Modules\Reporting\Infrastructure\Adapters\ReportingArchiveVisibilityAdapter;
use Illuminate\Support\ServiceProvider;

class ReportingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ReportingProvenanceFactory::class);
        $this->app->singleton(EntityTimelineSummaryBuilder::class);
        $this->app->singleton(ReportingArchiveVisibilityPort::class, ReportingArchiveVisibilityAdapter::class);
        $this->app->singleton(ReportingArchiveVisibilityGuard::class);
        $this->app->singleton(AuditHistorySourceReadPort::class, AuditHistorySourceReadAdapter::class);
        $this->app->singleton(QueryEntityAuditTimelineAction::class);
        $this->app->singleton(QueryActorAuditTimelineAction::class);
        $this->app->singleton(AggregateDrillDownPort::class, AggregateDrillDownAdapter::class);
        $this->app->singleton(ReportingReadService::class);
        $this->app->singleton(ReportingReadContract::class, ReportingReadService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(database_path('migrations/modules/reporting'));
    }
}
