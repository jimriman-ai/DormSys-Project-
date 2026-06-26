<?php

declare(strict_types=1);

namespace App\Modules\Identity\Application\Contracts;

use App\Modules\Identity\Application\DTOs\UserSummaryDTO;
use App\Modules\Identity\Domain\ValueObjects\UserId;

interface IdentityUserReadContract
{
    /**
     * Returns true if a user with the given id exists (any status).
     */
    public function userExists(UserId $id): bool;

    /**
     * Returns true if the user exists and status is Active.
     */
    public function isUserActive(UserId $id): bool;

    /**
     * Returns a minimal read projection, or null if not found.
     */
    public function findUserSummary(UserId $id): ?UserSummaryDTO;
}
