<?php

declare(strict_types=1);

namespace App\Modules\Audit\Application\Contracts;

interface AuditPrincipalContextPort
{
    public function currentPrincipalId(): ?string;
}

interface AuditPermissionReadPort
{
    public function principalHasAuditReadPermission(?string $principalId): bool;
}
