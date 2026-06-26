<?php

declare(strict_types=1);

namespace App\Modules\Employee\Infrastructure\Providers;

use App\Modules\Employee\Application\Contracts\DepartmentRepositoryContract;
use App\Modules\Employee\Application\Contracts\EmployeeRepositoryContract;
use App\Modules\Employee\Application\Services\AssignDepartmentToEmployeeAction;
use App\Modules\Employee\Application\Services\CreateDepartmentAction;
use App\Modules\Employee\Application\Services\CreateEmployeeAction;
use App\Modules\Employee\Application\Services\DeactivateDepartmentAction;
use App\Modules\Employee\Infrastructure\Repositories\DepartmentRepository;
use App\Modules\Employee\Infrastructure\Repositories\EmployeeRepository;
use Illuminate\Support\ServiceProvider;

class EmployeeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(EmployeeRepositoryContract::class, EmployeeRepository::class);
        $this->app->singleton(DepartmentRepositoryContract::class, DepartmentRepository::class);
        $this->app->singleton(CreateEmployeeAction::class);
        $this->app->singleton(CreateDepartmentAction::class);
        $this->app->singleton(DeactivateDepartmentAction::class);
        $this->app->singleton(AssignDepartmentToEmployeeAction::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(database_path('migrations/modules/employee'));
    }
}
