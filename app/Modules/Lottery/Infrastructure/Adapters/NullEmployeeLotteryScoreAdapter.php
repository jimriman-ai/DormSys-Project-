<?php

declare(strict_types=1);

namespace App\Modules\Lottery\Infrastructure\Adapters;

use App\Modules\Lottery\Application\Contracts\EmployeeLotteryScorePort;
use App\Modules\Lottery\Domain\ValueObjects\EmployeeReferenceId;

final class NullEmployeeLotteryScoreAdapter implements EmployeeLotteryScorePort
{
    public function baseScoreFor(EmployeeReferenceId $employeeId): float
    {
        return 0.0;
    }

    public function departmentPriorityFor(EmployeeReferenceId $employeeId): int
    {
        return 0;
    }
}
