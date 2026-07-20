<?php

declare(strict_types=1);

namespace App\Modules\Dormitory\Infrastructure\Providers;

use App\Modules\Dormitory\Application\Contracts\AllocationAssignabilityContract;
use App\Modules\Dormitory\Application\Contracts\AllocationBedPhysicalStateRepositoryContract;
use App\Modules\Dormitory\Application\Contracts\AllocationPhysicalStateApplicationContract;
use App\Modules\Dormitory\Application\Contracts\DormitoryStructureMutationContract;
use App\Modules\Dormitory\Application\Contracts\DormitoryStructureReadContract;
use App\Modules\Dormitory\Application\Contracts\DormitoryStructureReadRepositoryContract;
use App\Modules\Dormitory\Application\Contracts\DormitoryStructureWriteRepositoryContract;
use App\Modules\Dormitory\Infrastructure\Policies\DormitoryPolicy;
use App\Modules\Dormitory\Application\Services\AllocationAssignabilityService;
use App\Modules\Dormitory\Application\Services\AllocationPhysicalStateApplicationService;
use App\Modules\Dormitory\Application\Services\DormitoryStructureAuthorizationGate;
use App\Modules\Dormitory\Application\Services\DormitoryStructureMutationService;
use App\Modules\Dormitory\Application\Services\DormitoryStructureReadService;
use App\Modules\Dormitory\Domain\Contracts\DormitoryAssignmentReader;
use App\Modules\Dormitory\Infrastructure\Persistence\EloquentDormitoryAssignmentReader;
use App\Modules\Dormitory\Infrastructure\Persistence\Models\DormitoryModel;
use App\Modules\Dormitory\Infrastructure\Repositories\AllocationBedPhysicalStateRepository;
use App\Modules\Dormitory\Infrastructure\Repositories\DormitoryStructureReadRepository;
use App\Modules\Dormitory\Infrastructure\Repositories\DormitoryStructureWriteRepository;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class DormitoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(DormitoryAssignmentReader::class, EloquentDormitoryAssignmentReader::class);
        $this->app->singleton(DormitoryStructureReadRepositoryContract::class, DormitoryStructureReadRepository::class);
        $this->app->singleton(DormitoryStructureAuthorizationGate::class);
        $this->app->singleton(DormitoryStructureReadContract::class, DormitoryStructureReadService::class);
        $this->app->singleton(DormitoryStructureWriteRepositoryContract::class, DormitoryStructureWriteRepository::class);
        $this->app->singleton(DormitoryStructureMutationContract::class, DormitoryStructureMutationService::class);
        $this->app->singleton(AllocationBedPhysicalStateRepositoryContract::class, AllocationBedPhysicalStateRepository::class);
        $this->app->singleton(AllocationAssignabilityContract::class, AllocationAssignabilityService::class);
        $this->app->singleton(AllocationPhysicalStateApplicationContract::class, AllocationPhysicalStateApplicationService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(database_path('migrations/modules/dormitory'));

        Gate::policy(DormitoryModel::class, DormitoryPolicy::class);
    }
}
