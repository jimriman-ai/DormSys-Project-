<?php

declare(strict_types=1);

namespace App\Modules\Identity\Application\Services;

use App\Modules\Identity\Application\Contracts\IdentityUserReadContract;
use App\Modules\Identity\Application\Contracts\UserRepositoryContract;
use App\Modules\Identity\Application\DTOs\UserSummaryDTO;
use App\Modules\Identity\Domain\ValueObjects\UserId;

class IdentityUserReadService implements IdentityUserReadContract
{
    public function __construct(
        private readonly UserRepositoryContract $users,
    ) {}

    public function userExists(string $id): bool
    {
        return $this->users->findById(UserId::fromString($id)) !== null;
    }

    public function isUserActive(string $id): bool
    {
        $user = $this->users->findById(UserId::fromString($id));

        return $user !== null && $user->isActive();
    }

    public function findUserSummary(string $id): ?UserSummaryDTO
    {
        $user = $this->users->findById(UserId::fromString($id));

        if ($user === null) {
            return null;
        }

        return new UserSummaryDTO(
            id: $user->requireId()->value,
            status: $user->status->value,
            displayName: $user->displayName,
        );
    }

    public function userHasRole(string $userId, string $roleName): bool
    {
        return $this->users->userHasRole(UserId::fromString($userId), $roleName);
    }
}
