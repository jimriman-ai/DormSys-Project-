<?php

declare(strict_types=1);

namespace App\Modules\Identity\Infrastructure\Adapters;

use App\Modules\Audit\Application\Contracts\AuditPermissionReadPort;
use App\Modules\Identity\Infrastructure\Persistence\Models\UserModel;
use Database\Seeders\IdentityRoleSeeder;

final class SpatieAuditPermissionReadAdapter implements AuditPermissionReadPort
{
    public function principalHasAuditReadPermission(?string $principalId): bool
    {
        if ($principalId === null || $principalId === '') {
            return false;
        }

        $user = UserModel::query()->find($principalId);

        if ($user === null) {
            return false;
        }

        return $user->hasPermissionTo(IdentityRoleSeeder::PERMISSION_AUDIT_READ);
    }
}
