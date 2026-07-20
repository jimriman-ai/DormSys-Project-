<?php

declare(strict_types=1);

namespace App\Modules\Dormitory\Application\Policies;

use App\Shared\Auth\IdentityRoleGuard;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Q-DBT-1-AUTH Option B / WP-DASH-G02 — authorization for employee site listing (DASH-03 / DBT-1 residual).
 *
 * Abilities limited to viewAny. Role SoT: IdentityRoleGuard only (no string literals, no Dashboard imports).
 */
final class DormitoryPolicy
{
    /**
     * Whether the identity user may list dormitory sites (listSites / Employee Landing gate).
     */
    public function viewAny(Authenticatable $user): bool
    {
        return IdentityRoleGuard::userHasIdentityRole($user, IdentityRoleGuard::ROLE_EMPLOYEE);
    }
}
