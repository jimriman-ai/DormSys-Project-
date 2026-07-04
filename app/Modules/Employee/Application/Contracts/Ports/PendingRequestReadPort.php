<?php

declare(strict_types=1);

namespace App\Modules\Employee\Application\Contracts\Ports;

interface PendingRequestReadPort
{
    public function hasPendingRequest(string $employeeId, ?string $excludingRequestId = null): bool;
}
