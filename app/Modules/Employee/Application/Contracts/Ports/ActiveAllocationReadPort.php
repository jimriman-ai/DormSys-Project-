<?php

declare(strict_types=1);

namespace App\Modules\Employee\Application\Contracts\Ports;

use App\Modules\Employee\Domain\ValueObjects\EmployeeId;

interface ActiveAllocationReadPort
{
    public function hasActiveAllocation(EmployeeId $employeeId): bool;
}
