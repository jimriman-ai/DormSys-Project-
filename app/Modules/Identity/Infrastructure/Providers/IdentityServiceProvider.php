<?php

declare(strict_types=1);

namespace App\Modules\Identity\Infrastructure\Providers;

use App\Modules\Audit\Application\Contracts\AuditPermissionReadPort;
use App\Modules\Identity\Application\Contracts\IdentityUserReadContract;
use App\Modules\Identity\Application\Contracts\UserRepositoryContract;
use App\Modules\Identity\Application\Services\AssignRoleToUserAction;
use App\Modules\Identity\Application\Services\CreateUserAction;
use App\Modules\Identity\Application\Services\DeactivateUserAction;
use App\Modules\Identity\Application\Services\IdentityAuditEmitter;
use App\Modules\Identity\Application\Services\IdentityUserReadService;
use App\Modules\Identity\Application\Services\RevokeRoleFromUserAction;
use App\Modules\Identity\Infrastructure\Adapters\SpatieAuditPermissionReadAdapter;
use App\Modules\Identity\Infrastructure\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;

class IdentityServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(UserRepositoryContract::class, UserRepository::class);
        $this->app->singleton(IdentityUserReadContract::class, IdentityUserReadService::class);
        $this->app->singleton(IdentityAuditEmitter::class);
        $this->app->singleton(CreateUserAction::class);
        $this->app->singleton(DeactivateUserAction::class);
        $this->app->singleton(AssignRoleToUserAction::class);
        $this->app->singleton(RevokeRoleFromUserAction::class);
        $this->app->singleton(AuditPermissionReadPort::class, SpatieAuditPermissionReadAdapter::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(database_path('migrations/modules/identity'));
    }
}
