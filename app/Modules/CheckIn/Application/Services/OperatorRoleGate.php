<?php

declare(strict_types=1);

namespace App\Modules\CheckIn\Application\Services;

use App\Modules\CheckIn\Domain\CheckInOperationRoles;
use App\Modules\CheckIn\Domain\Exceptions\OperatorRoleRequiredException;
use App\Modules\Identity\Application\Contracts\UserRepositoryContract;
use App\Modules\Identity\Domain\ValueObjects\UserId;

final class OperatorRoleGate
{
    public function __construct(
        private readonly UserRepositoryContract $users,
    ) {}

    public function assertOperator(string $operatorId): void
    {
        if (! $this->users->userHasRole(
            UserId::fromString($operatorId),
            CheckInOperationRoles::OPERATOR,
        )) {
            throw new OperatorRoleRequiredException('Operator role is required for check-in and check-out.');
        }
    }
}
