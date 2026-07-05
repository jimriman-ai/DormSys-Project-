<?php

declare(strict_types=1);

namespace App\Modules\Identity\Infrastructure\Repositories;

use App\Modules\Identity\Application\Contracts\UserRepositoryContract;
use App\Modules\Identity\Domain\Entities\User;
use App\Modules\Identity\Domain\Exceptions\RoleNotFoundException;
use App\Modules\Identity\Domain\Exceptions\UserNotFoundException;
use App\Modules\Identity\Domain\PlatformRoles;
use App\Modules\Identity\Domain\ValueObjects\UserId;
use App\Modules\Identity\Infrastructure\Persistence\Models\UserModel;
use Spatie\Permission\Models\Role;

class UserRepository implements UserRepositoryContract
{
    public function save(User $user): User
    {
        if ($user->id === null) {
            $model = new UserModel([
                'status' => $user->status,
                'display_name' => $user->displayName,
                'email' => $user->email,
            ]);
            $model->save();

            return $this->toDomain($model);
        }

        $model = UserModel::query()->find($user->requireId()->value);

        if ($model === null) {
            $model = new UserModel;
            $model->setAttribute('id', $user->requireId()->value);
        }

        $model->fill([
            'status' => $user->status,
            'display_name' => $user->displayName,
            'email' => $user->email,
        ]);
        $model->save();

        return $this->toDomain($model);
    }

    public function findById(UserId $id): ?User
    {
        $model = UserModel::query()->find($id->value);

        return $model === null ? null : $this->toDomain($model);
    }

    public function existsByEmail(string $email): bool
    {
        return UserModel::query()->where('email', $email)->exists();
    }

    public function findByEmail(string $email): ?User
    {
        $model = UserModel::query()->where('email', $email)->first();

        return $model === null ? null : $this->toDomain($model);
    }

    public function countActiveSystemAdministrators(): int
    {
        return UserModel::query()
            ->active()
            ->role(PlatformRoles::SYSTEM_ADMINISTRATOR)
            ->count();
    }

    public function userHasRole(UserId $id, string $roleName): bool
    {
        $model = UserModel::query()->find($id->value);

        return $model !== null && $model->hasRole($roleName);
    }

    public function assignRole(UserId $id, string $roleName): void
    {
        $model = $this->findModelOrFail($id);
        $role = $this->findRoleOrFail($roleName);
        $model->assignRole($role);
    }

    public function revokeRole(UserId $id, string $roleName): void
    {
        $model = $this->findModelOrFail($id);
        $role = $this->findRoleOrFail($roleName);
        $model->removeRole($role);
    }

    private function findModelOrFail(UserId $id): UserModel
    {
        $model = UserModel::query()->find($id->value);

        if ($model === null) {
            throw new UserNotFoundException('User not found.');
        }

        return $model;
    }

    private function findRoleOrFail(string $roleName): Role
    {
        $role = Role::query()
            ->where('name', $roleName)
            ->where('guard_name', 'web')
            ->first();

        if ($role === null) {
            throw new RoleNotFoundException("Role [{$roleName}] does not exist.");
        }

        return $role;
    }

    private function toDomain(UserModel $model): User
    {
        return new User(
            id: UserId::fromString($model->getId()),
            status: $model->status,
            displayName: $model->display_name,
            email: $model->email,
        );
    }
}
