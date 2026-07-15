<?php

declare(strict_types=1);

namespace App\Modules\Identity\Application\Services;

use App\Application\Mutation\Registry\MutationCapabilityCatalog;
use App\Application\Mutation\Services\MutationPolicyEnforcementPoint;
use App\Modules\Identity\Application\Contracts\RoleRepositoryContract;
use App\Modules\Identity\Application\DTOs\RoleUserSummaryDTO;
use App\Modules\Identity\Domain\Exceptions\RoleNotFoundException;

final class ListRoleUsersAction
{
    public function __construct(
        private readonly RoleRepositoryContract $roles,
        private readonly MutationPolicyEnforcementPoint $mutationPolicy,
        private readonly IdentityMutationAuthorizationGate $identityMutationAuth,
    ) {}

    /**
     * @return list<RoleUserSummaryDTO>
     */
    public function execute(int $roleId): array
    {
        $this->mutationPolicy->enforce(MutationCapabilityCatalog::IDENTITY_ROLE_MANAGE, [
            'action' => 'list_users',
            'roleId' => $roleId,
        ]);
        $this->identityMutationAuth->assertManageRoles();

        if ($this->roles->findWebRoleById($roleId) === null) {
            throw new RoleNotFoundException("Role [{$roleId}] does not exist for guard web.");
        }

        return $this->roles->listUsersForWebRole($roleId);
    }
}
