<?php

declare(strict_types=1);

namespace App\Modules\Request\Application\Contracts;

/**
 * [PERMIT-ID: IMPL-PERMIT-02] Resolve Stage-1 approver identity for create-time snapshot.
 *
 * Resolution path: IdentityRoleGuard → active identity with ROLE_DORMITORY_MANAGER
 * (`dormitory-manager`, guard `identity`).
 */
interface Stage1ApproverIdentityReadContract
{
    /**
     * @return non-empty-string|null Identity user UUID, or null if unresolved
     */
    public function resolveActiveDormitoryManagerIdentityId(): ?string;
}
