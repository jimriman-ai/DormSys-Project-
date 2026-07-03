<?php

declare(strict_types=1);

namespace App\Modules\Notification\Application\Contracts;

interface EmployeeExistenceReadPort
{
    public function existsActiveEmployee(string $employeeId): bool;
}
