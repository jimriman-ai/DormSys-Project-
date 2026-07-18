<?php

declare(strict_types=1);

namespace App\Integrations\Request;

use App\Modules\Request\Application\Contracts\Stage1ApproverIdentityReadContract;
use App\Shared\Auth\IdentityRoleGuard;
use Database\Seeders\IdentityRoleSeeder;

/**
 * [PERMIT-ID: IMPL-PERMIT-02] Stage-1 snapshot via IdentityRoleGuard (Dormitory Manager).
 */
final class Stage1ApproverIdentityReadBridge implements Stage1ApproverIdentityReadContract
{
    public function resolveActiveDormitoryManagerIdentityId(): ?string
    {
        return IdentityRoleGuard::resolveActiveIdentityIdForRole(
            IdentityRoleSeeder::ROLE_DORMITORY_MANAGER,
        );
    }
}
