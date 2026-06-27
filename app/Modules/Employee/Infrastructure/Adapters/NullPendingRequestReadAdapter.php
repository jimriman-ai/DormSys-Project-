<?php

declare(strict_types=1);

namespace App\Modules\Employee\Infrastructure\Adapters;

use App\Modules\Employee\Application\Contracts\Ports\PendingRequestReadPort;
use App\Modules\Employee\Domain\ValueObjects\EmployeeId;

final class NullPendingRequestReadAdapter implements PendingRequestReadPort
{
    public function hasPendingRequest(EmployeeId $employeeId, ?string $excludingRequestId = null): bool
    {
        return false;
    }
}
