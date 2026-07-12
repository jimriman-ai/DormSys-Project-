<?php

declare(strict_types=1);

namespace App\Integrations\Audit;

use App\Modules\Audit\Application\Contracts\AuditPermissionReadPort;
use App\Modules\Identity\Application\Contracts\IdentityUserReadContract;
use Database\Seeders\IdentityRoleSeeder;

/**
 * Audit permission capability bridge (Identity read → AuditPermissionReadPort).
 *
 * Lives in app/Integrations per integration-layer-policy — not in Identity Infrastructure.
 */
final class SpatieAuditPermissionReadBridge implements AuditPermissionReadPort
{
    public function __construct(
        private readonly IdentityUserReadContract $identityUsers,
    ) {}

    public function principalHasAuditReadPermission(?string $principalId): bool
    {
        if ($principalId === null || $principalId === '') {
            return false;
        }

        return $this->identityUsers->userHasPermission(
            $principalId,
            IdentityRoleSeeder::PERMISSION_AUDIT_READ,
        );
    }
}
