<?php

declare(strict_types=1);

namespace App\Modules\Identity\Application\Contracts;

use App\Modules\Identity\Application\DTOs\RoleSummaryDTO;
use App\Modules\Identity\Application\DTOs\RoleUserSummaryDTO;

interface RoleRepositoryContract
{
    /**
     * @return list<RoleSummaryDTO>
     */
    public function listWebRoles(): array;

    public function findWebRoleById(int $roleId): ?RoleSummaryDTO;

    public function findWebRoleByName(string $name): ?RoleSummaryDTO;

    /**
     * @return list<RoleUserSummaryDTO>
     */
    public function listUsersForWebRole(int $roleId): array;

    public function countUsersForWebRole(int $roleId): int;

    public function createWebRole(string $name): RoleSummaryDTO;

    public function renameWebRole(int $roleId, string $name): RoleSummaryDTO;

    public function deleteWebRole(int $roleId): void;

    /**
     * @param  list<int>  $roleIds
     */
    public function syncUserWebRoles(string $userId, array $roleIds): void;

    /**
     * @param  list<int>  $roleIds
     * @return list<string>
     */
    public function resolveWebRoleNamesByIds(array $roleIds): array;
}
