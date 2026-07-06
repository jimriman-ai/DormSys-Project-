<?php

declare(strict_types=1);

namespace App\Modules\Lottery\Application\Services;

use App\Application\Mutation\Contracts\MutationPrincipalContextPort;
use App\Application\Mutation\Exceptions\UnauthorizedMutationException;
use App\Modules\Employee\Application\Contracts\EmployeeRepositoryContract;
use App\Modules\Identity\Application\Contracts\IdentityUserReadContract;
use App\Shared\ValueObjects\SystemActorId;

final class LotteryMutationAuthorizationGate
{
    public function __construct(
        private readonly MutationPrincipalContextPort $principalContext,
        private readonly IdentityUserReadContract $identityRead,
        private readonly EmployeeRepositoryContract $employees,
    ) {}

    public function assertManageProgram(): void
    {
        $this->assertActiveIdentityActorOrSystem();
    }

    public function assertEnrollOwn(string $employeeId): void
    {
        $principalId = $this->requirePrincipalId();
        $principalEmployeeId = $this->employees->findEmployeeIdByIdentityUserId($principalId);

        if ($principalEmployeeId === null || $principalEmployeeId !== $employeeId) {
            throw new UnauthorizedMutationException('Mutation actor must own the enrollment request.');
        }
    }

    private function assertActiveIdentityActorOrSystem(): void
    {
        $principalId = $this->requirePrincipalId();

        if ($principalId === SystemActorId::VALUE) {
            return;
        }

        if (! $this->identityRead->isUserActive($principalId)) {
            throw new UnauthorizedMutationException('Mutation actor must be an active identity user.');
        }
    }

    private function requirePrincipalId(): string
    {
        $principalId = $this->principalContext->currentPrincipalId();

        if ($principalId === null || $principalId === '') {
            throw new UnauthorizedMutationException('Mutation requires an authorized principal.');
        }

        return $principalId;
    }
}
