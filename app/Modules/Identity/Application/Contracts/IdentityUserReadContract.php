<?php

declare(strict_types=1);

namespace App\Modules\Identity\Application\Contracts;

use App\Modules\Identity\Application\DTOs\UserSummaryDTO;

interface IdentityUserReadContract
{
    /**
     * Returns true if a user with the given id exists (any status).
     */
    public function userExists(string $id): bool;

    /**
     * Returns true if the user exists and status is Active.
     */
    public function isUserActive(string $id): bool;

    /**
     * Returns a minimal read projection, or null if not found.
     */
    public function findUserSummary(string $id): ?UserSummaryDTO;

    /**
     * Returns true if the user exists and has the given role name.
     */
    public function userHasRole(string $userId, string $roleName): bool;

    /**
     * Returns true if the user exists and has the given permission name.
     *
     * Missing permission catalog rows are treated as false (never throws).
     */
    public function userHasPermission(string $userId, string $permissionName): bool;
}
