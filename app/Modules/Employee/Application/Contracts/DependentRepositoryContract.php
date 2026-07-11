<?php

declare(strict_types=1);

namespace App\Modules\Employee\Application\Contracts;

use App\Modules\Employee\Domain\Entities\Dependent;
use App\Modules\Employee\Domain\ValueObjects\DependentId;
use App\Modules\Employee\Domain\ValueObjects\EmployeeId;

interface DependentRepositoryContract
{
    public function save(Dependent $dependent): Dependent;

    public function findById(DependentId $id): ?Dependent;

    /**
     * @return list<Dependent>
     */
    public function listByEmployeeId(EmployeeId $employeeId): array;
}
