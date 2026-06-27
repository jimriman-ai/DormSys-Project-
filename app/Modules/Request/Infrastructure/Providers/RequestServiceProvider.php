<?php

declare(strict_types=1);

namespace App\Modules\Request\Infrastructure\Providers;

use App\Modules\Employee\Application\Contracts\Ports\PendingRequestReadPort;
use App\Modules\Request\Application\Contracts\DependentSnapshotRepositoryContract;
use App\Modules\Request\Application\Contracts\DependentSnapshotSourceContract;
use App\Modules\Request\Application\Contracts\DormitoryReadContract;
use App\Modules\Request\Application\Contracts\Internal\PendingRequestQueryPort;
use App\Modules\Request\Application\Contracts\Internal\RequestEligibilityGatewayContract;
use App\Modules\Request\Application\Contracts\Internal\RequestReadQueryPort;
use App\Modules\Request\Application\Contracts\MissionDetailsRepositoryContract;
use App\Modules\Request\Application\Contracts\RequestApprovalRepositoryContract;
use App\Modules\Request\Application\Contracts\RequestMemberRepositoryContract;
use App\Modules\Request\Application\Contracts\RequestReadContract;
use App\Modules\Request\Application\Contracts\RequestRepositoryContract;
use App\Modules\Request\Application\Services\ApproveRequestStageAction;
use App\Modules\Request\Application\Services\AutoApprovalSettingsReader;
use App\Modules\Request\Application\Services\CancelRequestAction;
use App\Modules\Request\Application\Services\CreateFamilyDirectRequestAction;
use App\Modules\Request\Application\Services\CreateLotteryRegistrationRequestAction;
use App\Modules\Request\Application\Services\CreateMissionRequestAction;
use App\Modules\Request\Application\Services\CreatePersonalRequestAction;
use App\Modules\Request\Application\Services\RejectRequestAction;
use App\Modules\Request\Application\Services\RequestCodeGenerator;
use App\Modules\Request\Application\Services\RequestReadService;
use App\Modules\Request\Application\Services\SubmitRequestAction;
use App\Modules\Request\Domain\Services\ApprovalStageResolver;
use App\Modules\Request\Domain\Services\MissionGroupValidator;
use App\Modules\Request\Infrastructure\Adapters\DependentSnapshotSourceStub;
use App\Modules\Request\Infrastructure\Adapters\EmployeeEligibilityGateway;
use App\Modules\Request\Infrastructure\Adapters\NullDormitoryReadAdapter;
use App\Modules\Request\Infrastructure\Adapters\PendingRequestReadAdapter;
use App\Modules\Request\Infrastructure\Queries\PendingRequestQuery;
use App\Modules\Request\Infrastructure\Queries\RequestReadQuery;
use App\Modules\Request\Infrastructure\Repositories\DependentSnapshotRepository;
use App\Modules\Request\Infrastructure\Repositories\MissionDetailsRepository;
use App\Modules\Request\Infrastructure\Repositories\RequestApprovalRepository;
use App\Modules\Request\Infrastructure\Repositories\RequestMemberRepository;
use App\Modules\Request\Infrastructure\Repositories\RequestRepository;
use Illuminate\Support\ServiceProvider;

class RequestServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(DependentSnapshotRepositoryContract::class, DependentSnapshotRepository::class);
        $this->app->singleton(RequestMemberRepositoryContract::class, RequestMemberRepository::class);
        $this->app->singleton(MissionDetailsRepositoryContract::class, MissionDetailsRepository::class);
        $this->app->singleton(MissionGroupValidator::class);
        $this->app->singleton(DependentSnapshotSourceStub::class);
        $this->app->singleton(DependentSnapshotSourceContract::class, DependentSnapshotSourceStub::class);
        $this->app->singleton(RequestRepositoryContract::class, RequestRepository::class);
        $this->app->singleton(RequestApprovalRepositoryContract::class, RequestApprovalRepository::class);
        $this->app->singleton(ApprovalStageResolver::class);
        $this->app->singleton(AutoApprovalSettingsReader::class);
        $this->app->singleton(RequestEligibilityGatewayContract::class, EmployeeEligibilityGateway::class);
        $this->app->singleton(RequestReadQueryPort::class, RequestReadQuery::class);
        $this->app->singleton(PendingRequestQueryPort::class, PendingRequestQuery::class);
        $this->app->singleton(RequestReadContract::class, RequestReadService::class);
        $this->app->singleton(PendingRequestReadPort::class, PendingRequestReadAdapter::class);
        $this->app->singleton(RequestCodeGenerator::class);
        $this->app->singleton(DormitoryReadContract::class, NullDormitoryReadAdapter::class);
        $this->app->singleton(CreatePersonalRequestAction::class);
        $this->app->singleton(CreateFamilyDirectRequestAction::class);
        $this->app->singleton(CreateMissionRequestAction::class);
        $this->app->singleton(CreateLotteryRegistrationRequestAction::class);
        $this->app->singleton(SubmitRequestAction::class);
        $this->app->singleton(CancelRequestAction::class);
        $this->app->singleton(ApproveRequestStageAction::class);
        $this->app->singleton(RejectRequestAction::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(database_path('migrations/modules/request'));
    }
}
