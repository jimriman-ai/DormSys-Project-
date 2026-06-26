<?php

declare(strict_types=1);

namespace App\Modules\Employee\Presentation\Console;

use App\Modules\Employee\Application\Services\AssignDepartmentToEmployeeAction;
use App\Modules\Employee\Domain\ValueObjects\DepartmentId;
use App\Modules\Employee\Domain\ValueObjects\EmployeeId;
use Illuminate\Console\Command;

class AssignDepartmentCommand extends Command
{
    protected $signature = 'department:assign
        {employee_id : Employee UUID}
        {department_id : Department UUID}';

    protected $description = 'Assign an employee to a department.';

    public function handle(AssignDepartmentToEmployeeAction $action): int
    {
        $employee = $action->execute(
            employeeId: EmployeeId::fromString((string) $this->argument('employee_id')),
            departmentId: DepartmentId::fromString((string) $this->argument('department_id')),
        );

        $this->info('Employee assigned to department: '.$employee->departmentId?->value);

        return self::SUCCESS;
    }
}
