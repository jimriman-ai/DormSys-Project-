<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Identity;

use App\Modules\Identity\Application\Contracts\IdentityUserReadContract;
use App\Modules\Identity\Domain\ValueObjects\UserId;

/**
 * Stand-in downstream consumer — depends only on IdentityUserReadContract (FR-011).
 */
final class StubEmployeeConsumer
{
    public function __construct(
        private readonly IdentityUserReadContract $identity,
    ) {}

    public function canReferenceUser(UserId $identityId): bool
    {
        return $this->identity->userExists($identityId->value)
            && $this->identity->isUserActive($identityId->value);
    }

    public function labelFor(UserId $identityId): ?string
    {
        return $this->identity->findUserSummary($identityId->value)?->displayName;
    }
}
