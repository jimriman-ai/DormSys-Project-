<?php

declare(strict_types=1);

namespace App\Modules\Notification\Infrastructure\Adapters;

use App\Modules\Notification\Application\Contracts\EmployeeExistenceReadPort;

final class InMemoryEmployeeExistenceReadAdapter implements EmployeeExistenceReadPort
{
    /**
     * @param  list<string>  $activeEmployeeIds
     */
    public function __construct(
        private array $activeEmployeeIds = [],
    ) {}

    public function existsActiveEmployee(string $employeeId): bool
    {
        return in_array($employeeId, $this->activeEmployeeIds, true);
    }
}
