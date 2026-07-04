<?php

declare(strict_types=1);

namespace App\Modules\Employee\Infrastructure\Adapters;

use App\Modules\Employee\Application\Contracts\Ports\PendingRequestReadPort;

final class NullPendingRequestReadAdapter implements PendingRequestReadPort
{
    public function hasPendingRequest(string $employeeId, ?string $excludingRequestId = null): bool
    {
        return false;
    }
}
