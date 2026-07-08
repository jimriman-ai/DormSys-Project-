<?php

declare(strict_types=1);

namespace App\Providers;

use App\Modules\Audit\Presentation\Http\Middleware\ResolveAuditPrincipalMiddleware;
use App\Modules\Request\Presentation\Http\Middleware\EnforceSessionMutationPrincipalMiddleware;
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
    }
}
