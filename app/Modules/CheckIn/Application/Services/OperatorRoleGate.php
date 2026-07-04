<?php

declare(strict_types=1);

namespace App\Modules\CheckIn\Application\Services;

use App\Modules\CheckIn\Domain\CheckInOperationRoles;
use App\Modules\CheckIn\Domain\Exceptions\OperatorRoleRequiredException;
use App\Modules\Identity\Application\Contracts\IdentityUserReadContract;

final class OperatorRoleGate
{
    public function __construct(
        private readonly IdentityUserReadContract $identityRead,
    ) {}

    public function assertOperator(string $operatorId): void
    {
        if (! $this->identityRead->userHasRole($operatorId, CheckInOperationRoles::OPERATOR)) {
            throw new OperatorRoleRequiredException('Operator role is required for check-in and check-out.');
        }
    }
}
