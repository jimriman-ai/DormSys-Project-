<?php

declare(strict_types=1);

namespace App\Modules\Lottery\Application\Contracts;

use App\Modules\Lottery\Domain\ValueObjects\EmployeeReferenceId;

interface EmployeeLotteryScorePort
{
    public function baseScoreFor(EmployeeReferenceId $employeeId): float;

    public function departmentPriorityFor(EmployeeReferenceId $employeeId): int;
}
