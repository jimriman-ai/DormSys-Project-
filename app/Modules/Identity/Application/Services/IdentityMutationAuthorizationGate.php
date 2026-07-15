<?php

declare(strict_types=1);

namespace App\Modules\Identity\Application\Services;

use App\Application\Mutation\Contracts\MutationPrincipalContextPort;
use App\Application\Mutation\Exceptions\UnauthorizedMutationException;
use App\Modules\Identity\Application\Contracts\IdentityUserReadContract;

final class IdentityMutationAuthorizationGate
{
    /**
     * Actor authority for role assignment (Spatie permission).
     * Distinct from MutationCapabilityCatalog::IDENTITY_ROLE_ASSIGN.
     */
    private const string ACTOR_PERMISSION_ROLES_MANAGE = 'identity.roles.manage';

    public function __construct(
        private readonly MutationPrincipalContextPort $principalContext,
        private readonly IdentityUserReadContract $identityRead,
    ) {}

    public function assertCreate(): void
    {
        $this->requirePrincipalId();
    }

    public function assertDeactivate(): void
    {
        $this->assertActiveIdentityActor();
    }

    public function assertAssignRole(): void
    {
        $principalId = $this->assertActiveIdentityActor();

        if (! $this->identityRead->userHasPermission($principalId, self::ACTOR_PERMISSION_ROLES_MANAGE)) {
            throw new UnauthorizedMutationException(
                'Mutation actor must hold identity.roles.manage to assign roles.',
            );
        }
    }

    public function assertManageRoles(): void
    {
        $principalId = $this->assertActiveIdentityActor();

        if (! $this->identityRead->userHasPermission($principalId, self::ACTOR_PERMISSION_ROLES_MANAGE)) {
            throw new UnauthorizedMutationException(
                'Mutation actor must hold identity.roles.manage to manage roles.',
            );
        }
    }

    public function assertRevokeRole(): void
    {
        $this->assertActiveIdentityActor();
    }

    private function assertActiveIdentityActor(): string
    {
        $principalId = $this->requirePrincipalId();

        if (! $this->identityRead->isUserActive($principalId)) {
            throw new UnauthorizedMutationException('Mutation actor must be an active identity user.');
        }

        return $principalId;
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
