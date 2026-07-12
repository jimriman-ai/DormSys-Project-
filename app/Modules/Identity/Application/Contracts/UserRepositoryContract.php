<?php

declare(strict_types=1);

namespace App\Modules\Identity\Application\Contracts;

use App\Modules\Identity\Domain\Entities\User;
use App\Modules\Identity\Domain\ValueObjects\UserId;

interface UserRepositoryContract
{
    public function save(User $user): User;

    public function findById(UserId $id): ?User;

    public function existsByEmail(string $email): bool;

    public function findByEmail(string $email): ?User;

    public function countActiveSystemAdministrators(): int;

    public function userHasRole(UserId $id, string $roleName): bool;

    /**
     * Missing permission catalog rows are treated as false (never throws).
     */
    public function userHasPermission(UserId $id, string $permissionName): bool;

    public function assignRole(UserId $id, string $roleName): void;

    public function revokeRole(UserId $id, string $roleName): void;
}
