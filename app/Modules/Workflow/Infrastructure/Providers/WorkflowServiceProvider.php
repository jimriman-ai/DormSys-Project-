<?php

declare(strict_types=1);

namespace App\Modules\Workflow\Infrastructure\Providers;

use App\Modules\Workflow\Application\Contracts\RequestApprovalWorkflowRepositoryContract;
use App\Modules\Workflow\Application\Services\ApplyRequestApprovalAutoApprovalsAction;
use App\Modules\Workflow\Application\Services\DecideRequestApprovalStageAction;
use App\Modules\Workflow\Application\Services\StartRequestApprovalWorkflowAction;
use App\Modules\Workflow\Domain\Services\FixedRequestApprovalStageRolePolicy;
use App\Modules\Workflow\Infrastructure\Repositories\EloquentRequestApprovalWorkflowRepository;
use Illuminate\Support\ServiceProvider;

class WorkflowServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            RequestApprovalWorkflowRepositoryContract::class,
            EloquentRequestApprovalWorkflowRepository::class,
        );
        $this->app->singleton(FixedRequestApprovalStageRolePolicy::class);
        $this->app->singleton(StartRequestApprovalWorkflowAction::class);
        $this->app->singleton(ApplyRequestApprovalAutoApprovalsAction::class);
        $this->app->singleton(DecideRequestApprovalStageAction::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(database_path('migrations/modules/workflow'));
    }
}
