<?php

declare(strict_types=1);

namespace App\Modules\Identity\Application\Services;

use App\Application\Mutation\Registry\MutationCapabilityCatalog;
use App\Application\Mutation\Services\MutationPolicyEnforcementPoint;
use App\Modules\Identity\Application\Contracts\RoleRepositoryContract;
use App\Modules\Identity\Application\DTOs\RoleSummaryDTO;

final class CreateRoleAction
{
    public function __construct(
        private readonly RoleRepositoryContract $roles,
        private readonly MutationPolicyEnforcementPoint $mutationPolicy,
        private readonly IdentityMutationAuthorizationGate $identityMutationAuth,
        private readonly IdentityAuditEmitter $auditEmitter,
    ) {}

    public function execute(string $name): RoleSummaryDTO
    {
        $this->mutationPolicy->enforce(MutationCapabilityCatalog::IDENTITY_ROLE_MANAGE, [
            'action' => 'create',
            'name' => $name,
        ]);
        $this->identityMutationAuth->assertManageRoles();

        $role = $this->roles->createWebRole($name);

        $this->auditEmitter->recordRoleCreated(
            $role->id,
            $role->name,
            IdentityAuditEmitter::occurredNow(),
        );

        return $role;
    }
}
