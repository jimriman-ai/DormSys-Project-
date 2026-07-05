<?php

declare(strict_types=1);

namespace App\Modules\Audit\Application\Services;

use App\Modules\Audit\Application\Contracts\AuditAuthorizationPort;

final class AuditReadPolicyEnforcementPoint
{
    public function __construct(
        private readonly AuditAuthorizationPort $authorization,
    ) {}

    public function enforce(): void
    {
        $this->authorization->authorizeRead();
    }
}
