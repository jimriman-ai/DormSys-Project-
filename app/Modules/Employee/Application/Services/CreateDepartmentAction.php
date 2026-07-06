<?php

declare(strict_types=1);

namespace App\Modules\Employee\Application\Services;

use App\Application\Mutation\Registry\MutationCapabilityCatalog;
use App\Application\Mutation\Services\MutationPolicyEnforcementPoint;
use App\Modules\Employee\Application\Contracts\DepartmentRepositoryContract;
use App\Modules\Employee\Application\Contracts\EmployeeRepositoryContract;
use App\Modules\Employee\Domain\Entities\Department;
use App\Modules\Employee\Domain\Events\DepartmentCreated;
use App\Modules\Employee\Domain\Exceptions\DepartmentNotFoundException;
use App\Modules\Employee\Domain\Exceptions\DuplicateDepartmentCodeException;
use App\Modules\Employee\Domain\Exceptions\EmployeeNotFoundException;
use App\Modules\Employee\Domain\ValueObjects\DepartmentId;
use App\Modules\Employee\Domain\ValueObjects\EmployeeId;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

final class CreateDepartmentAction
{
    public function __construct(
        private readonly DepartmentRepositoryContract $departments,
        private readonly EmployeeRepositoryContract $employees,
        private readonly MutationPolicyEnforcementPoint $mutationPolicy,
        private readonly EmployeeMutationAuthorizationGate $employeeMutationAuth,
    ) {}

    public function execute(
        string $name,
        string $code,
        ?EmployeeId $managerId = null,
        ?DepartmentId $parentId = null,
        int $lotteryPriority = 0,
    ): Department {
        $this->mutationPolicy->enforce(MutationCapabilityCatalog::EMPLOYEE_DEPARTMENT_CREATE, [
            'code' => $code,
        ]);
        $this->employeeMutationAuth->assertCreateDepartment();

        if ($this->departments->existsByCode($code)) {
            throw new DuplicateDepartmentCodeException('A department with this code already exists.');
        }

        if ($managerId !== null && $this->employees->findById($managerId) === null) {
            throw new EmployeeNotFoundException('Manager employee not found.');
        }

        if ($parentId !== null && $this->departments->findById($parentId) === null) {
            throw new DepartmentNotFoundException('Parent department not found.');
        }

        return DB::transaction(function () use (
            $name,
            $code,
            $managerId,
            $parentId,
            $lotteryPriority,
        ): Department {
            $department = Department::createNew(
                name: $name,
                code: $code,
                managerId: $managerId,
                parentId: $parentId,
                lotteryPriority: $lotteryPriority,
            );

            $persisted = $this->departments->save($department);

            Event::dispatch(DepartmentCreated::forDepartment(
                departmentId: $persisted->requireId()->value,
                code: $persisted->code,
            ));

            return $persisted;
        });
    }
}
