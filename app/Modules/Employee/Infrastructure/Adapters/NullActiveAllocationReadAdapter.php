<?php

declare(strict_types=1);

namespace App\Modules\Employee\Infrastructure\Adapters;

use App\Modules\Employee\Application\Contracts\Ports\ActiveAllocationReadPort;
use App\Modules\Employee\Domain\ValueObjects\EmployeeId;

final class NullActiveAllocationReadAdapter implements ActiveAllocationReadPort
{
    public function hasActiveAllocation(EmployeeId $employeeId): bool
    {
        return false;
    }
}
