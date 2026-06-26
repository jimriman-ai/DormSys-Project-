<?php

declare(strict_types=1);

namespace App\Modules\Employee\Application\Contracts;

use App\Modules\Employee\Domain\Entities\Employee;
use App\Modules\Employee\Domain\ValueObjects\EmployeeId;
use App\Modules\Identity\Domain\ValueObjects\UserId;

interface EmployeeRepositoryContract
{
    public function save(Employee $employee): Employee;

    public function findById(EmployeeId $id): ?Employee;

    public function findByIdentityId(UserId $identityId): ?Employee;

    public function existsByIdentityId(UserId $identityId): bool;
}
