<?php

declare(strict_types=1);

namespace App\Modules\Dormitory\Infrastructure\Providers;

use App\Modules\Dormitory\Application\Contracts\DormitoryStructureMutationContract;
use App\Modules\Dormitory\Application\Contracts\DormitoryStructureReadContract;
use App\Modules\Dormitory\Application\Contracts\DormitoryStructureReadRepositoryContract;
use App\Modules\Dormitory\Application\Contracts\DormitoryStructureWriteRepositoryContract;
use App\Modules\Dormitory\Application\Services\DormitoryStructureMutationService;
use App\Modules\Dormitory\Application\Services\DormitoryStructureReadService;
use App\Modules\Dormitory\Infrastructure\Repositories\DormitoryStructureReadRepository;
use App\Modules\Dormitory\Infrastructure\Repositories\DormitoryStructureWriteRepository;
use Illuminate\Support\ServiceProvider;

class DormitoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(DormitoryStructureReadRepositoryContract::class, DormitoryStructureReadRepository::class);
        $this->app->singleton(DormitoryStructureReadContract::class, DormitoryStructureReadService::class);
        $this->app->singleton(DormitoryStructureWriteRepositoryContract::class, DormitoryStructureWriteRepository::class);
        $this->app->singleton(DormitoryStructureMutationContract::class, DormitoryStructureMutationService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(database_path('migrations/modules/dormitory'));
    }
}
