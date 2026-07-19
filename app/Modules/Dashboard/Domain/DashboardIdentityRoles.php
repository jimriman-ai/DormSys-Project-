<?php

declare(strict_types=1);

namespace App\Modules\Dashboard\Domain;

/**
 * Sprint C dashboard nav identity-guard role slugs (D2 scope).
 *
 * EMPLOYEE value must match the Spatie identity-guard role slug `employee`
 * (same string as identity role catalog / IdentityRoleGuard consumers use).
 */
final class DashboardIdentityRoles
{
    public const string EMPLOYEE = 'employee';
}
