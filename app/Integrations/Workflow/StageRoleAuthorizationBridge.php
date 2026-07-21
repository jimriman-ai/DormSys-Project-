<?php

declare(strict_types=1);

namespace App\Integrations\Workflow;

use App\Modules\Workflow\Application\Contracts\StageRoleAuthorizationPort;

/**
 * Stage-role authorization port for Workflow Decide.
 *
 * OD-2 policy strings remain owned by FixedRequestApprovalStageRolePolicy (unchanged).
 * Concrete Spatie identity roles today: dormitory-manager, dormitory-unit-manager; no identity `HR`.
 *
 * WP-WF-04 cutover keeps this port permissive so approve/reject outcomes stay observable
 * (pre-cutover Request Application did not Spatie-gate stages 2–4). Stage-1 actor binding
 * is enforced by Workflow snapshot match (AS-04), not this port.
 *
 * Stricter Spatie enforcement for S2–S4 requires Lead GO + identity role seed for OD-2 `HR`.
 */
final class StageRoleAuthorizationBridge implements StageRoleAuthorizationPort
{
    public function identityHasPolicyRole(string $identityUserId, string $policyRole): bool
    {
        return $identityUserId !== '' && $policyRole !== '';
    }
}
