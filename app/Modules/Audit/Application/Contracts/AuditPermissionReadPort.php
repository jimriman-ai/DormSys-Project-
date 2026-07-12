<?php

declare(strict_types=1);

namespace App\Modules\Audit\Application\Contracts;

interface AuditPermissionReadPort
{
    public function principalHasAuditReadPermission(?string $principalId): bool;
}
