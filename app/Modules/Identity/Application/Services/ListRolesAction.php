<?php

declare(strict_types=1);

namespace App\Modules\Identity\Application\Services;

use App\Application\Mutation\Registry\MutationCapabilityCatalog;
use App\Application\Mutation\Services\MutationPolicyEnforcementPoint;
use App\Modules\Identity\Application\Contracts\RoleRepositoryContract;
use App\Modules\Identity\Application\DTOs\RoleSummaryDTO;

final class ListRolesAction
{
    public function __construct(
        private readonly RoleRepositoryContract $roles,
        private readonly MutationPolicyEnforcementPoint $mutationPolicy,
        private readonly IdentityMutationAuthorizationGate $identityMutationAuth,
    ) {}

    /**
     * @return list<RoleSummaryDTO>
     */
    public function execute(): array
    {
        $this->mutationPolicy->enforce(MutationCapabilityCatalog::IDENTITY_ROLE_MANAGE, [
            'action' => 'list',
        ]);
        $this->identityMutationAuth->assertManageRoles();

        return $this->roles->listWebRoles();
    }
}
