<?php

declare(strict_types=1);

namespace App\Modules\Employee\Presentation\Providers;

use App\Modules\Employee\Presentation\Console\AssignDepartmentCommand;
use App\Modules\Employee\Presentation\Console\CreateDepartmentCommand;
use App\Modules\Employee\Presentation\Console\CreateEmployeeCommand;
use Illuminate\Support\ServiceProvider;

class EmployeePresentationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                CreateEmployeeCommand::class,
                CreateDepartmentCommand::class,
                AssignDepartmentCommand::class,
            ]);
        }
    }
}
