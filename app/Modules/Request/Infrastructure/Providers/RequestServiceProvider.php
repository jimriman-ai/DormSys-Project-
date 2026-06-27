<?php

declare(strict_types=1);

namespace App\Modules\Request\Infrastructure\Providers;

use App\Modules\Request\Application\Contracts\RequestRepositoryContract;
use App\Modules\Request\Application\Services\RequestCodeGenerator;
use App\Modules\Request\Infrastructure\Repositories\RequestRepository;
use Illuminate\Support\ServiceProvider;

class RequestServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(RequestRepositoryContract::class, RequestRepository::class);
        $this->app->singleton(RequestCodeGenerator::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(database_path('migrations/modules/request'));
    }
}
