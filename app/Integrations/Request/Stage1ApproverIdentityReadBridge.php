<?php

declare(strict_types=1);

namespace App\Integrations\Request;

use App\Modules\Employee\Application\Contracts\DepartmentRepositoryContract;
use App\Modules\Employee\Application\Contracts\EmployeeRepositoryContract;
use App\Modules\Employee\Domain\ValueObjects\EmployeeId;
use App\Modules\Request\Application\Contracts\Stage1ApproverIdentityReadContract;

/**
 * [PERMIT-ID: IMPL-PERMIT-02] Org-chart bridge for Stage-1 snapshot.
 *
 * employee_employees → employee_departments.manager_id → manager.identity_id
 */
final class Stage1ApproverIdentityReadBridge implements Stage1ApproverIdentityReadContract
{
    public function __construct(
        private readonly EmployeeRepositoryContract $employees,
        private readonly DepartmentRepositoryContract $departments,
    ) {}

    public function resolveForEmployee(string $employeeId): ?string
    {
        $employee = $this->employees->findById(EmployeeId::fromString($employeeId));

        if ($employee === null || $employee->departmentId === null) {
            return null;
        }

        $department = $this->departments->findById($employee->departmentId);

        if ($department === null || $department->managerId === null) {
            return null;
        }

        $manager = $this->employees->findById($department->managerId);

        if ($manager === null) {
            return null;
        }

        return $manager->identityId->value;
    }
}
