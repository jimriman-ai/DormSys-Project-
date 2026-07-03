<?php

declare(strict_types=1);

namespace App\Modules\Notification\Infrastructure\Adapters;

use App\Modules\Notification\Application\Contracts\EmployeeExistenceReadPort;
use Ramsey\Uuid\Uuid;

final class StubEmployeeExistenceReadAdapter implements EmployeeExistenceReadPort
{
    public function existsActiveEmployee(string $employeeId): bool
    {
        return Uuid::isValid($employeeId);
    }
}
