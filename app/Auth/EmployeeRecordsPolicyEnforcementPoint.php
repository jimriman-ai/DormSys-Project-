<?php

declare(strict_types=1);

namespace App\Auth;

use App\Modules\Identity\Infrastructure\Persistence\Models\UserModel;
use Database\Seeders\IdentityRoleSeeder;

/**
 * HR Manager / employee_records enforcement (PolicyEnforcementPoint pattern).
 *
 * Subject for employee_records.* is the Employee aggregate (Employee module).
 * No Employee entity. Allocation person_id abstractions remain unchanged.
 *
 * Conflict note: Spatie roles/permissions for guard `web` attach to
 * {@see UserModel}, not {@see \App\Models\User} (credential login model without HasRoles).
 */
final class EmployeeRecordsPolicyEnforcementPoint
{
    public function canRead(?UserModel $user): bool
    {
        if ($user === null) {
            return false;
        }

        return $user->checkPermissionTo(IdentityRoleSeeder::PERMISSION_EMPLOYEE_RECORDS_READ);
    }

    public function canEdit(?UserModel $user): bool
    {
        if ($user === null) {
            return false;
        }

        return $user->checkPermissionTo(IdentityRoleSeeder::PERMISSION_EMPLOYEE_RECORDS_EDIT);
    }
}
