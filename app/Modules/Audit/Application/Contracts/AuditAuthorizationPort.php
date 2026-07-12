<?php

declare(strict_types=1);

namespace App\Modules\Audit\Application\Contracts;

interface AuditAuthorizationPort
{
    public function authorizeRead(): void;
}
