<?php

declare(strict_types=1);

namespace App\Modules\Request\Application\Contracts;

/**
 * [PERMIT-ID: IMPL-PERMIT-02] Resolve Stage-1 approver identity for snapshot at create time.
 *
 * Lookup path (chosen): employee → department → manager_id (EmployeeId) → manager.identity_id
 *
 * Ambiguity note: IMPL-PERMIT-02 prose mentions "Dormitory Manager"; Spec04 DGAP-05 A /
 * OQ-AUTH-01 bind Stage-1 to department line manager (DeptMgr) via org-chart
 * employee_departments.manager_id. Dormitory-manager Spatie role is a different surface
 * (dormitory-admin). This contract follows the department org-chart path.
 */
interface Stage1ApproverIdentityReadContract
{
    /**
     * @return non-empty-string|null Identity user UUID, or null if unresolved
     */
    public function resolveForEmployee(string $employeeId): ?string;
}
