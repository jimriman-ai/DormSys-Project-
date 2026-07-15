<?php

declare(strict_types=1);

namespace App\Modules\Identity\Infrastructure\Repositories;

use App\Modules\Identity\Application\Contracts\RoleRepositoryContract;
use App\Modules\Identity\Application\DTOs\RoleSummaryDTO;
use App\Modules\Identity\Application\DTOs\RoleUserSummaryDTO;
use App\Modules\Identity\Domain\Exceptions\RoleHasAssignedUsersException;
use App\Modules\Identity\Domain\Exceptions\RoleNotFoundException;
use App\Modules\Identity\Domain\Exceptions\UserNotFoundException;
use App\Modules\Identity\Infrastructure\Persistence\Models\UserModel;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

final class RoleRepository implements RoleRepositoryContract
{
    private const string GUARD = 'web';

    public function listWebRoles(): array
    {
        $roles = [];

        foreach (
            Role::query()
                ->where('guard_name', self::GUARD)
                ->orderBy('name')
                ->get() as $role
        ) {
            if (! $role instanceof Role) {
                continue;
            }

            $roles[] = $this->toSummary($role);
        }

        return $roles;
    }

    public function findWebRoleById(int $roleId): ?RoleSummaryDTO
    {
        $role = $this->findRoleModelById($roleId);

        return $role === null ? null : $this->toSummary($role);
    }

    public function findWebRoleByName(string $name): ?RoleSummaryDTO
    {
        $role = Role::query()
            ->where('name', $name)
            ->where('guard_name', self::GUARD)
            ->first();

        return $role === null ? null : $this->toSummary($role);
    }

    public function listUsersForWebRole(int $roleId): array
    {
        $role = $this->findRoleModelByIdOrFail($roleId);
        $users = [];

        foreach (
            UserModel::query()
                ->role($role)
                ->orderBy('display_name')
                ->get() as $user
        ) {
            if (! $user instanceof UserModel) {
                continue;
            }

            $users[] = new RoleUserSummaryDTO(
                id: $user->id,
                displayName: $user->display_name,
                email: $user->email,
                status: $user->status->value,
            );
        }

        return $users;
    }

    public function countUsersForWebRole(int $roleId): int
    {
        $role = $this->findRoleModelByIdOrFail($roleId);

        return UserModel::query()->role($role)->count();
    }

    public function createWebRole(string $name): RoleSummaryDTO
    {
        Role::create([
            'name' => $name,
            'guard_name' => self::GUARD,
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $created = $this->findRoleModelByNameOrFail($name);

        return $this->toSummary($created);
    }

    public function renameWebRole(int $roleId, string $name): RoleSummaryDTO
    {
        $role = $this->findRoleModelByIdOrFail($roleId);
        $role->name = $name;
        $role->save();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $fresh = $this->findRoleModelById((int) $role->id);

        return $this->toSummary($fresh ?? $role);
    }

    public function deleteWebRole(int $roleId): void
    {
        DB::transaction(function () use ($roleId): void {
            $role = Role::query()
                ->where('id', $roleId)
                ->where('guard_name', self::GUARD)
                ->lockForUpdate()
                ->first();

            if ($role === null) {
                throw new RoleNotFoundException("Role [{$roleId}] does not exist for guard web.");
            }

            if (UserModel::query()->role($role)->count() > 0) {
                throw new RoleHasAssignedUsersException(
                    'Role cannot be deleted while users are assigned. Detach users first.',
                );
            }

            $role->delete();
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function syncUserWebRoles(string $userId, array $roleIds): void
    {
        $model = UserModel::query()->find($userId);

        if ($model === null) {
            throw new UserNotFoundException('User not found.');
        }

        $roles = Role::query()
            ->where('guard_name', self::GUARD)
            ->whereIn('id', $roleIds)
            ->get();

        if ($roles->count() !== count(array_unique($roleIds))) {
            throw new RoleNotFoundException('One or more roles do not exist for guard web.');
        }

        DB::transaction(static function () use ($model, $roles): void {
            $model->syncRoles($roles);
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function resolveWebRoleNamesByIds(array $roleIds): array
    {
        if ($roleIds === []) {
            return [];
        }

        $names = [];

        foreach (
            Role::query()
                ->where('guard_name', self::GUARD)
                ->whereIn('id', $roleIds)
                ->pluck('name') as $name
        ) {
            $names[] = (string) $name;
        }

        return $names;
    }

    private function findRoleModelById(int $roleId): ?Role
    {
        return Role::query()
            ->where('id', $roleId)
            ->where('guard_name', self::GUARD)
            ->first();
    }

    private function findRoleModelByIdOrFail(int $roleId): Role
    {
        $role = $this->findRoleModelById($roleId);

        if ($role === null) {
            throw new RoleNotFoundException("Role [{$roleId}] does not exist for guard web.");
        }

        return $role;
    }

    private function findRoleModelByNameOrFail(string $name): Role
    {
        $role = Role::query()
            ->where('name', $name)
            ->where('guard_name', self::GUARD)
            ->first();

        if ($role === null) {
            throw new RoleNotFoundException("Role [{$name}] does not exist for guard web.");
        }

        return $role;
    }

    private function toSummary(Role $role): RoleSummaryDTO
    {
        $usersCount = UserModel::query()->role($role)->count();

        return new RoleSummaryDTO(
            id: (int) $role->id,
            name: (string) $role->name,
            guardName: (string) $role->guard_name,
            usersCount: $usersCount,
        );
    }
}
