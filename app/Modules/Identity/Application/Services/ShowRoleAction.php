<?php

declare(strict_types=1);

namespace App\Modules\Identity\Application\Services;

use App\Application\Mutation\Registry\MutationCapabilityCatalog;
use App\Application\Mutation\Services\MutationPolicyEnforcementPoint;
use App\Modules\Identity\Application\Contracts\RoleRepositoryContract;
use App\Modules\Identity\Application\DTOs\RoleSummaryDTO;
use App\Modules\Identity\Domain\Exceptions\RoleNotFoundException;

final class ShowRoleAction
{
    public function __construct(
        private readonly RoleRepositoryContract $roles,
        private readonly MutationPolicyEnforcementPoint $mutationPolicy,
        private readonly IdentityMutationAuthorizationGate $identityMutationAuth,
    ) {}

    public function execute(int $roleId): RoleSummaryDTO
    {
        $this->mutationPolicy->enforce(MutationCapabilityCatalog::IDENTITY_ROLE_MANAGE, [
            'action' => 'show',
            'roleId' => $roleId,
        ]);
        $this->identityMutationAuth->assertManageRoles();

        $role = $this->roles->findWebRoleById($roleId);

        if ($role === null) {
            throw new RoleNotFoundException("Role [{$roleId}] does not exist for guard web.");
        }

        return $role;
    }
}
