<?php

declare(strict_types=1);

namespace App\Modules\Employee\Application\Contracts;

use App\Modules\Employee\Domain\Entities\Department;
use App\Modules\Employee\Domain\ValueObjects\DepartmentId;

interface DepartmentRepositoryContract
{
    public function save(Department $department): Department;

    public function findById(DepartmentId $id): ?Department;

    public function existsByCode(string $code): bool;
}
