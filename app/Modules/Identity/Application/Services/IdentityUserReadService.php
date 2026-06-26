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

    public function userExists(UserId $id): bool
    {
        return $this->users->findById($id) !== null;
    }

    public function isUserActive(UserId $id): bool
    {
        $user = $this->users->findById($id);

        return $user !== null && $user->isActive();
    }

    public function findUserSummary(UserId $id): ?UserSummaryDTO
    {
        $user = $this->users->findById($id);

        if ($user === null) {
            return null;
        }

        return new UserSummaryDTO(
            id: $user->requireId()->value,
            status: $user->status->value,
            displayName: $user->displayName,
        );
    }
}
