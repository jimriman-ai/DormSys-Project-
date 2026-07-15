<?php

declare(strict_types=1);

namespace App\Modules\Identity\Infrastructure\Providers;

use App\Modules\Identity\Application\Contracts\IdentityUserReadContract;
use App\Modules\Identity\Application\Contracts\RoleRepositoryContract;
use App\Modules\Identity\Application\Contracts\UserRepositoryContract;
use App\Modules\Identity\Application\Services\AssignRoleToUserAction;
use App\Modules\Identity\Application\Services\CreateRoleAction;
use App\Modules\Identity\Application\Services\CreateUserAction;
use App\Modules\Identity\Application\Services\DeactivateUserAction;
use App\Modules\Identity\Application\Services\DeleteRoleAction;
use App\Modules\Identity\Application\Services\IdentityAuditEmitter;
use App\Modules\Identity\Application\Services\IdentityMutationAuthorizationGate;
use App\Modules\Identity\Application\Services\IdentityUserReadService;
use App\Modules\Identity\Application\Services\ListRolesAction;
use App\Modules\Identity\Application\Services\ListRoleUsersAction;
use App\Modules\Identity\Application\Services\RenameRoleAction;
use App\Modules\Identity\Application\Services\RevokeRoleFromUserAction;
use App\Modules\Identity\Application\Services\ShowRoleAction;
use App\Modules\Identity\Application\Services\SyncUserRolesAction;
use App\Modules\Identity\Infrastructure\Repositories\RoleRepository;
use App\Modules\Identity\Infrastructure\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;

class IdentityServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(UserRepositoryContract::class, UserRepository::class);
        $this->app->singleton(RoleRepositoryContract::class, RoleRepository::class);
        $this->app->singleton(IdentityUserReadContract::class, IdentityUserReadService::class);
        $this->app->singleton(IdentityAuditEmitter::class);
        $this->app->singleton(IdentityMutationAuthorizationGate::class);
        $this->app->singleton(CreateUserAction::class);
        $this->app->singleton(DeactivateUserAction::class);
        $this->app->singleton(AssignRoleToUserAction::class);
        $this->app->singleton(RevokeRoleFromUserAction::class);
        $this->app->singleton(CreateRoleAction::class);
        $this->app->singleton(RenameRoleAction::class);
        $this->app->singleton(DeleteRoleAction::class);
        $this->app->singleton(SyncUserRolesAction::class);
        $this->app->singleton(ListRolesAction::class);
        $this->app->singleton(ShowRoleAction::class);
        $this->app->singleton(ListRoleUsersAction::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(database_path('migrations/modules/identity'));
    }
}
