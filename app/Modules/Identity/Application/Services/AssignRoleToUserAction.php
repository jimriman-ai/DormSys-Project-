<?php

declare(strict_types=1);

namespace App\Modules\Identity\Application\Services;

use App\Application\Mutation\Registry\MutationCapabilityCatalog;
use App\Application\Mutation\Services\MutationPolicyEnforcementPoint;
use App\Modules\Identity\Application\Contracts\UserRepositoryContract;
use App\Modules\Identity\Domain\Exceptions\UserNotFoundException;
use App\Modules\Identity\Domain\ValueObjects\UserId;

class AssignRoleToUserAction
{
    public function __construct(
        private readonly UserRepositoryContract $users,
        private readonly IdentityAuditEmitter $auditEmitter,
        private readonly MutationPolicyEnforcementPoint $mutationPolicy,
        private readonly IdentityMutationAuthorizationGate $identityMutationAuth,
    ) {}

    public function execute(UserId $userId, string $roleName): void
    {
        $this->mutationPolicy->enforce(MutationCapabilityCatalog::IDENTITY_ROLE_ASSIGN, [
            'userId' => $userId->value,
            'roleName' => $roleName,
        ]);
        $this->identityMutationAuth->assertAssignRole();

        if ($this->users->findById($userId) === null) {
            throw new UserNotFoundException('User not found.');
        }

        $this->users->assignRole($userId, $roleName);

        $this->auditEmitter->recordRoleAssigned(
            $userId,
            $roleName,
            IdentityAuditEmitter::occurredNow(),
        );
    }
}
