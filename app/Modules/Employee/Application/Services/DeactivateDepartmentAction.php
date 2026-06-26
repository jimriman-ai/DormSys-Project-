<?php

declare(strict_types=1);

namespace App\Modules\Employee\Application\Services;

use App\Modules\Employee\Application\Contracts\DepartmentRepositoryContract;
use App\Modules\Employee\Domain\Entities\Department;
use App\Modules\Employee\Domain\Exceptions\DepartmentNotFoundException;
use App\Modules\Employee\Domain\ValueObjects\DepartmentId;
use Illuminate\Support\Facades\DB;

final class DeactivateDepartmentAction
{
    public function __construct(
        private readonly DepartmentRepositoryContract $departments,
    ) {}

    public function execute(DepartmentId $departmentId): Department
    {
        $department = $this->departments->findById($departmentId);

        if ($department === null) {
            throw new DepartmentNotFoundException('Department not found.');
        }

        return DB::transaction(function () use ($department): Department {
            $department->deactivate();

            return $this->departments->save($department);
        });
    }
}
