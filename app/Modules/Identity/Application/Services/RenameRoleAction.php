<?php

declare(strict_types=1);

namespace App\Modules\Identity\Application\Services;

use App\Application\Mutation\Registry\MutationCapabilityCatalog;
use App\Application\Mutation\Services\MutationPolicyEnforcementPoint;
use App\Modules\Identity\Application\Contracts\RoleRepositoryContract;
use App\Modules\Identity\Application\DTOs\RoleSummaryDTO;
use App\Modules\Identity\Domain\Exceptions\ProtectedRoleException;
use App\Modules\Identity\Domain\Exceptions\RoleNotFoundException;
use App\Modules\Identity\Domain\PlatformRoles;

final class RenameRoleAction
{
    public function __construct(
        private readonly RoleRepositoryContract $roles,
        private readonly MutationPolicyEnforcementPoint $mutationPolicy,
        private readonly IdentityMutationAuthorizationGate $identityMutationAuth,
        private readonly IdentityAuditEmitter $auditEmitter,
    ) {}

    public function execute(int $roleId, string $name): RoleSummaryDTO
    {
        $this->mutationPolicy->enforce(MutationCapabilityCatalog::IDENTITY_ROLE_MANAGE, [
            'action' => 'rename',
            'roleId' => $roleId,
            'name' => $name,
        ]);
        $this->identityMutationAuth->assertManageRoles();

        $existing = $this->roles->findWebRoleById($roleId);

        if ($existing === null) {
            throw new RoleNotFoundException("Role [{$roleId}] does not exist for guard web.");
        }

        if ($existing->name === PlatformRoles::SYSTEM_ADMINISTRATOR) {
            throw new ProtectedRoleException('SystemAdministrator role cannot be renamed.');
        }

        $role = $this->roles->renameWebRole($roleId, $name);

        $this->auditEmitter->recordRoleUpdated(
            $role->id,
            $existing->name,
            $role->name,
            IdentityAuditEmitter::occurredNow(),
        );

        return $role;
    }
}
