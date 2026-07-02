<?php

declare(strict_types=1);

namespace App\Modules\Audit\Infrastructure\Adapters;

use App\Modules\Audit\Application\Contracts\AuditAuthorizationPort;
use App\Modules\Audit\Application\Contracts\AuditPermissionReadPort;
use App\Modules\Audit\Application\Contracts\AuditPrincipalContextPort;
use App\Modules\Audit\Domain\Exceptions\UnauthorizedAuditAccessException;

final class AuditAuthorizationAdapter implements AuditAuthorizationPort
{
    public function __construct(
        private readonly AuditPrincipalContextPort $principalContext,
        private readonly AuditPermissionReadPort $permissionRead,
    ) {}

    public function authorizeRead(): void
    {
        $principalId = $this->principalContext->currentPrincipalId();

        if (! $this->permissionRead->principalHasAuditReadPermission($principalId)) {
            throw new UnauthorizedAuditAccessException('Unauthorized audit history access.');
        }
    }
}
