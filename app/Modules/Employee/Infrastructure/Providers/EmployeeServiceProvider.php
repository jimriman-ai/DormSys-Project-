<?php

declare(strict_types=1);

namespace App\Modules\Employee\Infrastructure\Providers;

use App\Modules\Employee\Application\Contracts\EmployeeRepositoryContract;
use App\Modules\Employee\Application\Services\CreateEmployeeAction;
use App\Modules\Employee\Infrastructure\Repositories\EmployeeRepository;
use Illuminate\Support\ServiceProvider;

class EmployeeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(EmployeeRepositoryContract::class, EmployeeRepository::class);
        $this->app->singleton(CreateEmployeeAction::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(database_path('migrations/modules/employee'));
    }
}
