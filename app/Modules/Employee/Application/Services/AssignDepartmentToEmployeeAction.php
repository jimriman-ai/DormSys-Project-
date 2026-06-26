<?php

declare(strict_types=1);

namespace App\Modules\Employee\Application\Services;

use App\Modules\Employee\Application\Contracts\DepartmentRepositoryContract;
use App\Modules\Employee\Application\Contracts\EmployeeRepositoryContract;
use App\Modules\Employee\Domain\Entities\Employee;
use App\Modules\Employee\Domain\Exceptions\DepartmentNotFoundException;
use App\Modules\Employee\Domain\Exceptions\EmployeeNotFoundException;
use App\Modules\Employee\Domain\Exceptions\InactiveDepartmentAssignmentException;
use App\Modules\Employee\Domain\ValueObjects\DepartmentId;
use App\Modules\Employee\Domain\ValueObjects\EmployeeId;
use Illuminate\Support\Facades\DB;

final class AssignDepartmentToEmployeeAction
{
    public function __construct(
        private readonly EmployeeRepositoryContract $employees,
        private readonly DepartmentRepositoryContract $departments,
    ) {}

    public function execute(EmployeeId $employeeId, DepartmentId $departmentId): Employee
    {
        $employee = $this->employees->findById($employeeId);

        if ($employee === null) {
            throw new EmployeeNotFoundException('Employee not found.');
        }

        $department = $this->departments->findById($departmentId);

        if ($department === null) {
            throw new DepartmentNotFoundException('Department not found.');
        }

        if (! $department->isActive()) {
            throw new InactiveDepartmentAssignmentException('Cannot assign employee to an inactive department.');
        }

        return DB::transaction(function () use ($employee, $departmentId): Employee {
            $updated = $employee->assignDepartment($departmentId);

            return $this->employees->save($updated);
        });
    }
}
