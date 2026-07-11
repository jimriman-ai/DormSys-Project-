<?php

declare(strict_types=1);

namespace App\Modules\Employee\Application\Services;

use App\Application\Mutation\Contracts\MutationPrincipalContextPort;
use App\Application\Mutation\Exceptions\UnauthorizedMutationException;
use App\Modules\Identity\Application\Contracts\IdentityUserReadContract;

final class EmployeeMutationAuthorizationGate
{
    public function __construct(
        private readonly MutationPrincipalContextPort $principalContext,
        private readonly IdentityUserReadContract $identityRead,
    ) {}

    public function assertCreateEmployee(): void
    {
        $this->assertActiveIdentityActor();
    }

    public function assertCreateDepartment(): void
    {
        $this->assertActiveIdentityActor();
    }

    public function assertDeactivateDepartment(): void
    {
        $this->assertActiveIdentityActor();
    }

    public function assertAssignDepartment(): void
    {
        $this->assertActiveIdentityActor();
    }

    public function assertAddDependent(): void
    {
        $this->assertActiveIdentityActor();
    }

    public function assertUpdateDependent(): void
    {
        $this->assertActiveIdentityActor();
    }

    private function assertActiveIdentityActor(): void
    {
        $principalId = $this->requirePrincipalId();

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
