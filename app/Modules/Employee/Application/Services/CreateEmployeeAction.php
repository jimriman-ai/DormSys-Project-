<?php

declare(strict_types=1);

namespace App\Modules\Employee\Application\Services;

use App\Modules\Employee\Application\Contracts\EmployeeRepositoryContract;
use App\Modules\Employee\Domain\Entities\Employee;
use App\Modules\Employee\Domain\Events\EmployeeCreated;
use App\Modules\Employee\Domain\Exceptions\DuplicateIdentityIdException;
use App\Modules\Employee\Domain\Exceptions\UnknownIdentityUserException;
use App\Modules\Identity\Application\Contracts\IdentityUserReadContract;
use App\Modules\Identity\Domain\ValueObjects\UserId;
use App\Support\ValueObjects\Identity\NationalCode;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

final class CreateEmployeeAction
{
    public function __construct(
        private readonly IdentityUserReadContract $identityRead,
        private readonly EmployeeRepositoryContract $employees,
    ) {}

    public function execute(
        UserId $identityId,
        string $employeeCode,
        string $firstName,
        string $lastName,
        NationalCode $nationalCode,
        DateTimeImmutable $hireDate,
    ): Employee {
        if (! $this->identityRead->userExists($identityId)) {
            throw new UnknownIdentityUserException('Identity user does not exist.');
        }

        if ($this->employees->existsByIdentityId($identityId)) {
            throw new DuplicateIdentityIdException('An employee already exists for this identity_id.');
        }

        return DB::transaction(function () use (
            $identityId,
            $employeeCode,
            $firstName,
            $lastName,
            $nationalCode,
            $hireDate,
        ): Employee {
            $employee = Employee::createNew(
                identityId: $identityId,
                employeeCode: $employeeCode,
                firstName: $firstName,
                lastName: $lastName,
                nationalCode: $nationalCode,
                hireDate: $hireDate,
            );

            $persisted = $this->employees->save($employee);

            Event::dispatch(EmployeeCreated::forEmployee(
                employeeId: $persisted->requireId()->value,
                identityId: $persisted->identityId->value,
            ));

            return $persisted;
        });
    }
}
