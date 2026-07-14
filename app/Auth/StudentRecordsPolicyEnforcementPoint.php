<?php

declare(strict_types=1);

namespace App\Auth;

use App\Modules\Identity\Infrastructure\Persistence\Models\UserModel;
use Database\Seeders\IdentityRoleSeeder;

/**
 * HR Manager / student_records enforcement (PolicyEnforcementPoint pattern).
 *
 * Subject/model binding for student_records.* is deferred (no Student entity).
 *
 * Conflict note: Spatie roles/permissions for guard `web` attach to
 * {@see UserModel}, not {@see \App\Models\User} (credential login model without HasRoles).
 */
final class StudentRecordsPolicyEnforcementPoint
{
    public function canRead(?UserModel $user): bool
    {
        if ($user === null) {
            return false;
        }

        return $user->checkPermissionTo(IdentityRoleSeeder::PERMISSION_STUDENT_RECORDS_READ);
    }

    public function canEdit(?UserModel $user): bool
    {
        if ($user === null) {
            return false;
        }

        return $user->checkPermissionTo(IdentityRoleSeeder::PERMISSION_STUDENT_RECORDS_EDIT);
    }
}
