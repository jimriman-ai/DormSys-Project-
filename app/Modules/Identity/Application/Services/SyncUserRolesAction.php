<?php

declare(strict_types=1);

namespace App\Modules\Identity\Application\Services;

use App\Application\Mutation\Contracts\MutationPrincipalContextPort;
use App\Application\Mutation\Registry\MutationCapabilityCatalog;
use App\Application\Mutation\Services\MutationPolicyEnforcementPoint;
use App\Modules\Identity\Application\Contracts\RoleRepositoryContract;
use App\Modules\Identity\Application\Contracts\UserRepositoryContract;
use App\Modules\Identity\Domain\Exceptions\CannotRemoveOwnSystemAdministratorRoleException;
use App\Modules\Identity\Domain\Exceptions\LastSystemAdministratorException;
use App\Modules\Identity\Domain\Exceptions\UserNotFoundException;
use App\Modules\Identity\Domain\PlatformRoles;
use App\Modules\Identity\Domain\ValueObjects\UserId;

final class SyncUserRolesAction
{
    public function __construct(
        private readonly RoleRepositoryContract $roles,
        private readonly UserRepositoryContract $users,
        private readonly MutationPolicyEnforcementPoint $mutationPolicy,
        private readonly IdentityMutationAuthorizationGate $identityMutationAuth,
        private readonly MutationPrincipalContextPort $principalContext,
        private readonly IdentityAuditEmitter $auditEmitter,
    ) {}

    /**
     * @param  list<int>  $roleIds
     */
    public function execute(string $userId, array $roleIds): void
    {
        $this->mutationPolicy->enforce(MutationCapabilityCatalog::IDENTITY_ROLE_MANAGE, [
            'action' => 'sync_user_roles',
            'userId' => $userId,
            'roleIds' => $roleIds,
        ]);
        $this->identityMutationAuth->assertManageRoles();

        $targetId = UserId::fromString($userId);
        $user = $this->users->findById($targetId);

        if ($user === null) {
            throw new UserNotFoundException('User not found.');
        }

        $targetRoleNames = $this->roles->resolveWebRoleNamesByIds($roleIds);
        $hadSystemAdministrator = $this->users->userHasRole(
            $targetId,
            PlatformRoles::SYSTEM_ADMINISTRATOR,
        );
        $willHaveSystemAdministrator = in_array(PlatformRoles::SYSTEM_ADMINISTRATOR, $targetRoleNames, true);

        if ($hadSystemAdministrator && ! $willHaveSystemAdministrator) {
            if ($this->users->countSystemAdministratorsExcluding($targetId) === 0) {
                throw new LastSystemAdministratorException(
                    'Cannot remove SystemAdministrator from the last remaining holder.',
                );
            }

            $actorId = $this->principalContext->currentPrincipalId();

            if ($actorId !== null && $actorId === $userId) {
                throw new CannotRemoveOwnSystemAdministratorRoleException(
                    'Cannot remove SystemAdministrator role from yourself.',
                );
            }
        }

        $this->roles->syncUserWebRoles($userId, $roleIds);

        $this->auditEmitter->recordUserRolesSynced(
            $targetId,
            $targetRoleNames,
            IdentityAuditEmitter::occurredNow(),
        );
    }
}
