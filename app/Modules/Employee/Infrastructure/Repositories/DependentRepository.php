<?php

declare(strict_types=1);

namespace App\Modules\Employee\Infrastructure\Repositories;

use App\Modules\Employee\Application\Contracts\DependentRepositoryContract;
use App\Modules\Employee\Domain\Entities\Dependent;
use App\Modules\Employee\Domain\Exceptions\DependentNotFoundException;
use App\Modules\Employee\Domain\ValueObjects\DependentId;
use App\Modules\Employee\Domain\ValueObjects\EmployeeId;
use App\Modules\Employee\Infrastructure\Persistence\Models\DependentModel;
use App\Support\ValueObjects\Identity\NationalCode;

class DependentRepository implements DependentRepositoryContract
{
    public function save(Dependent $dependent): Dependent
    {
        if ($dependent->id === null) {
            $model = new DependentModel([
                'employee_id' => $dependent->employeeId->value,
                'first_name' => $dependent->firstName,
                'last_name' => $dependent->lastName,
                'relationship' => $dependent->relationship,
                'age' => $dependent->age,
                'national_code' => $dependent->nationalCode?->value,
            ]);
            $model->save();

            return $this->toDomain($model);
        }

        $model = DependentModel::query()->find($dependent->requireId()->value);

        if ($model === null) {
            throw new DependentNotFoundException('Dependent not found.');
        }

        $model->fill([
            'employee_id' => $dependent->employeeId->value,
            'first_name' => $dependent->firstName,
            'last_name' => $dependent->lastName,
            'relationship' => $dependent->relationship,
            'age' => $dependent->age,
            'national_code' => $dependent->nationalCode?->value,
        ]);
        $model->save();

        return $this->toDomain($model);
    }

    public function findById(DependentId $id): ?Dependent
    {
        $model = DependentModel::query()->find($id->value);

        return $model === null ? null : $this->toDomain($model);
    }

    public function listByEmployeeId(EmployeeId $employeeId): array
    {
        $dependents = DependentModel::query()
            ->where('employee_id', $employeeId->value)
            ->orderBy('created_at')
            ->get()
            ->map(fn (DependentModel $model): Dependent => $this->toDomain($model))
            ->all();

        return array_values($dependents);
    }

    private function toDomain(DependentModel $model): Dependent
    {
        return new Dependent(
            id: DependentId::fromString($model->getId()),
            employeeId: EmployeeId::fromString($model->employee_id),
            firstName: $model->first_name,
            lastName: $model->last_name,
            relationship: $model->relationship,
            age: $model->age,
            nationalCode: $model->national_code !== null
                ? NationalCode::fromString($model->national_code)
                : null,
        );
    }
}
