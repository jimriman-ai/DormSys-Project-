<?php

declare(strict_types=1);

namespace App\Modules\Audit\Infrastructure\Providers;

use App\Modules\Audit\Application\Contracts\AuditAuthorizationPort;
use App\Modules\Audit\Application\Contracts\AuditHistoryReadContract;
use App\Modules\Audit\Application\Contracts\AuditLogRepositoryContract;
use App\Modules\Audit\Application\Contracts\AuditPrincipalContextPort;
use App\Modules\Audit\Application\Contracts\AuditRecordingContract;
use App\Modules\Audit\Application\Services\AuditHistoryReadService;
use App\Modules\Audit\Application\Contracts\AuditEventTypeCatalogPort;
use App\Modules\Audit\Application\Services\AuditEventTypeCatalog;
use App\Modules\Audit\Application\Services\PayloadHashCalculator;
use App\Modules\Audit\Application\Services\QueryAuditHistoryAction;
use App\Modules\Audit\Application\Services\RecordAuditAction;
use App\Modules\Audit\Infrastructure\Adapters\AuditAuthorizationAdapter;
use App\Modules\Audit\Infrastructure\Adapters\RequestAuditPrincipalContext;
use App\Modules\Audit\Infrastructure\Listeners\ActivityLogAuditBridge;
use App\Modules\Audit\Infrastructure\Repositories\AuditLogRepository;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Spatie\Activitylog\Models\Activity;

class AuditServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AuditLogRepositoryContract::class, AuditLogRepository::class);
        $this->app->singleton(PayloadHashCalculator::class);

        $this->app->singleton(RecordAuditAction::class);
        $this->app->singleton(AuditRecordingContract::class, RecordAuditAction::class);

        $this->app->singleton(AuditPrincipalContextPort::class, RequestAuditPrincipalContext::class);
        $this->app->singleton(AuditAuthorizationPort::class, AuditAuthorizationAdapter::class);
        $this->app->singleton(QueryAuditHistoryAction::class);
        $this->app->singleton(AuditHistoryReadService::class);
        $this->app->singleton(AuditHistoryReadContract::class, AuditHistoryReadService::class);
        $this->app->singleton(AuditEventTypeCatalogPort::class, AuditEventTypeCatalog::class);
        $this->app->singleton(AuditRetentionSettingsReader::class);
        $this->app->singleton(ActivityLogAuditBridge::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(database_path('migrations/modules/audit'));

        if ((bool) config('audit.activity_bridge_enabled', false)) {
            $activityModel = config('activitylog.activity_model', Activity::class);

            Event::listen(
                'eloquent.created: '.$activityModel,
                function (Activity $activity): void {
                    app(ActivityLogAuditBridge::class)->handle($activity);
                },
            );
        }
    }
}
