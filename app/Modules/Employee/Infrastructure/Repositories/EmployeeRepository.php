<?php

declare(strict_types=1);

namespace App\Modules\Employee\Infrastructure\Repositories;

use App\Modules\Employee\Application\Contracts\EmployeeRepositoryContract;
use App\Modules\Employee\Domain\Entities\Employee;
use App\Modules\Employee\Domain\Exceptions\EmployeeNotFoundException;
use App\Modules\Employee\Domain\Exceptions\IdentityIdImmutableException;
use App\Modules\Employee\Domain\ValueObjects\DepartmentId;
use App\Modules\Employee\Domain\ValueObjects\EmployeeId;
use App\Modules\Employee\Domain\ValueObjects\IdentityUserId;
use App\Modules\Employee\Infrastructure\Persistence\Models\EmployeeModel;
use App\Support\ValueObjects\Identity\NationalCode;
use DateTimeImmutable;

class EmployeeRepository implements EmployeeRepositoryContract
{
    public function save(Employee $employee): Employee
    {
        if ($employee->id === null) {
            $model = new EmployeeModel([
                'employee_code' => $employee->employeeCode,
                'first_name' => $employee->firstName,
                'last_name' => $employee->lastName,
                'national_code' => $employee->nationalCode->toString(),
                'department_id' => $employee->departmentId?->value,
                'hire_date' => $employee->hireDate->format('Y-m-d'),
                'base_lottery_score' => $employee->baseLotteryScore,
                'status' => $employee->status,
            ]);
            $model->identity_id = $employee->identityId->value;
            $model->save();

            return $this->toDomain($model);
        }

        $model = EmployeeModel::query()->find($employee->requireId()->value);

        if ($model === null) {
            throw new EmployeeNotFoundException('Employee not found.');
        }

        $this->assertIdentityIdImmutable($model, $employee->identityId);

        $model->fill([
            'employee_code' => $employee->employeeCode,
            'first_name' => $employee->firstName,
            'last_name' => $employee->lastName,
            'national_code' => $employee->nationalCode->toString(),
            'department_id' => $employee->departmentId?->value,
            'hire_date' => $employee->hireDate->format('Y-m-d'),
            'base_lottery_score' => $employee->baseLotteryScore,
            'status' => $employee->status,
        ]);
        $model->save();

        return $this->toDomain($model);
    }

    public function findById(EmployeeId $id): ?Employee
    {
        $model = EmployeeModel::query()->find($id->value);

        return $model === null ? null : $this->toDomain($model);
    }

    public function findByIdentityId(IdentityUserId $identityId): ?Employee
    {
        $model = EmployeeModel::query()
            ->where('identity_id', $identityId->value)
            ->first();

        return $model === null ? null : $this->toDomain($model);
    }

    public function existsByIdentityId(IdentityUserId $identityId): bool
    {
        return EmployeeModel::query()
            ->where('identity_id', $identityId->value)
            ->exists();
    }

    public function assertIdentityIdImmutable(EmployeeModel $model, IdentityUserId $identityId): void
    {
        if ($model->identity_id !== $identityId->value) {
            throw new IdentityIdImmutableException('identity_id cannot be changed after assignment.');
        }
    }

    private function toDomain(EmployeeModel $model): Employee
    {
        return new Employee(
            id: EmployeeId::fromString($model->getId()),
            identityId: IdentityUserId::fromString($model->identity_id),
            employeeCode: $model->employee_code,
            firstName: $model->first_name,
            lastName: $model->last_name,
            nationalCode: NationalCode::fromString($model->national_code),
            departmentId: $model->department_id !== null
                ? DepartmentId::fromString($model->department_id)
                : null,
            hireDate: new DateTimeImmutable($model->hire_date->format('Y-m-d')),
            baseLotteryScore: (int) $model->base_lottery_score,
            status: $model->status,
        );
    }
}
