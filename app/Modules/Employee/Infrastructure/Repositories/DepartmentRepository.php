<?php

declare(strict_types=1);

namespace App\Modules\Employee\Infrastructure\Repositories;

use App\Modules\Employee\Application\Contracts\DepartmentRepositoryContract;
use App\Modules\Employee\Domain\Entities\Department;
use App\Modules\Employee\Domain\Exceptions\DepartmentNotFoundException;
use App\Modules\Employee\Domain\ValueObjects\DepartmentId;
use App\Modules\Employee\Domain\ValueObjects\EmployeeId;
use App\Modules\Employee\Infrastructure\Persistence\Models\DepartmentModel;

class DepartmentRepository implements DepartmentRepositoryContract
{
    public function save(Department $department): Department
    {
        if ($department->id === null) {
            $model = new DepartmentModel([
                'name' => $department->name,
                'code' => $department->code,
                'manager_id' => $department->managerId?->value,
                'parent_id' => $department->parentId?->value,
                'lottery_priority' => $department->lotteryPriority,
                'status' => $department->status,
            ]);
            $model->save();

            return $this->toDomain($model);
        }

        $model = DepartmentModel::query()->find($department->requireId()->value);

        if ($model === null) {
            throw new DepartmentNotFoundException('Department not found.');
        }

        $model->fill([
            'name' => $department->name,
            'code' => $department->code,
            'manager_id' => $department->managerId?->value,
            'parent_id' => $department->parentId?->value,
            'lottery_priority' => $department->lotteryPriority,
            'status' => $department->status,
        ]);
        $model->save();

        return $this->toDomain($model);
    }

    public function findById(DepartmentId $id): ?Department
    {
        $model = DepartmentModel::query()->find($id->value);

        return $model === null ? null : $this->toDomain($model);
    }

    public function existsByCode(string $code): bool
    {
        return DepartmentModel::query()
            ->where('code', $code)
            ->exists();
    }

    private function toDomain(DepartmentModel $model): Department
    {
        return new Department(
            id: DepartmentId::fromString($model->getId()),
            name: $model->name,
            code: $model->code,
            managerId: $model->manager_id !== null
                ? EmployeeId::fromString($model->manager_id)
                : null,
            parentId: $model->parent_id !== null
                ? DepartmentId::fromString($model->parent_id)
                : null,
            lotteryPriority: (int) $model->lottery_priority,
            status: $model->status,
        );
    }
}
