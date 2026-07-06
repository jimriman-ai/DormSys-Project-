<?php

declare(strict_types=1);

namespace App\Modules\Allocation\Application\Services;

use App\Application\Mutation\Contracts\MutationPrincipalContextPort;
use App\Application\Mutation\Exceptions\UnauthorizedMutationException;
use App\Modules\Identity\Application\Contracts\IdentityUserReadContract;
use App\Shared\ValueObjects\SystemActorId;

final class AllocationMutationAuthorizationGate
{
    public function __construct(
        private readonly MutationPrincipalContextPort $principalContext,
        private readonly IdentityUserReadContract $identityRead,
    ) {}

    public function assertCreate(): void
    {
        $this->assertActiveIdentityActor();
    }

    public function assertCreateFromRequest(): void
    {
        $this->assertActiveIdentityActor();
    }

    public function assertRelease(): void
    {
        $this->assertActiveIdentityActor();
    }

    private function assertActiveIdentityActor(): void
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
