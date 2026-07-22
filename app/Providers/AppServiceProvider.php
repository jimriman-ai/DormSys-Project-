<?php

declare(strict_types=1);

namespace App\Providers;

use App\Integrations\Notification\RequestApprovalNotificationSubscriber;
use App\Modules\Audit\Presentation\Http\Middleware\ResolveAuditPrincipalMiddleware;
use App\Modules\Request\Domain\Events\RequestApproved;
use App\Modules\Request\Domain\Events\RequestRejected;
use App\Modules\Request\Domain\Events\RequestSubmitted;
use App\Modules\Request\Presentation\Http\Middleware\EnforceSessionMutationPrincipalMiddleware;
use App\Modules\Workflow\Domain\Events\WorkflowInstanceCompleted;
use App\Modules\Workflow\Domain\Events\WorkflowInstanceRejected;
use App\Modules\Workflow\Domain\Events\WorkflowStepActivated;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Laravel\Telescope\TelescopeServiceProvider as TelescopePackageServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->environment('local')) {
            $this->app->register(TelescopePackageServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Livewire::addPersistentMiddleware([
            EnforceSessionMutationPrincipalMiddleware::class,
            ResolveAuditPrincipalMiddleware::class,
        ]);

        // Deferred class listeners (do not resolve NotificationDeliveryContract at boot).
        Event::listen(RequestSubmitted::class, [RequestApprovalNotificationSubscriber::class, 'onRequestSubmitted']);
        Event::listen(WorkflowStepActivated::class, [RequestApprovalNotificationSubscriber::class, 'onWorkflowStepActivated']);
        Event::listen(RequestApproved::class, [RequestApprovalNotificationSubscriber::class, 'onRequestApproved']);
        Event::listen(WorkflowInstanceCompleted::class, [RequestApprovalNotificationSubscriber::class, 'onWorkflowInstanceCompleted']);
        Event::listen(RequestRejected::class, [RequestApprovalNotificationSubscriber::class, 'onRequestRejected']);
        Event::listen(WorkflowInstanceRejected::class, [RequestApprovalNotificationSubscriber::class, 'onWorkflowInstanceRejected']);
    }
}
