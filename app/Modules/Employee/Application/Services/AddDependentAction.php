<?php

declare(strict_types=1);

namespace App\Modules\Employee\Application\Services;

use App\Application\Mutation\Registry\MutationCapabilityCatalog;
use App\Application\Mutation\Services\MutationPolicyEnforcementPoint;
use App\Modules\Employee\Application\Contracts\DependentRepositoryContract;
use App\Modules\Employee\Application\Contracts\EmployeeRepositoryContract;
use App\Modules\Employee\Domain\Entities\Dependent;
use App\Modules\Employee\Domain\Enums\DependentRelationship;
use App\Modules\Employee\Domain\Exceptions\EmployeeNotFoundException;
use App\Modules\Employee\Domain\ValueObjects\EmployeeId;
use App\Support\ValueObjects\Identity\NationalCode;
use Illuminate\Support\Facades\DB;

final class AddDependentAction
{
    public function __construct(
        private readonly DependentRepositoryContract $dependents,
        private readonly EmployeeRepositoryContract $employees,
        private readonly MutationPolicyEnforcementPoint $mutationPolicy,
        private readonly EmployeeMutationAuthorizationGate $employeeMutationAuth,
    ) {}

    public function execute(
        EmployeeId $employeeId,
        string $firstName,
        string $lastName,
        DependentRelationship $relationship,
        ?int $age = null,
        ?string $nationalCode = null,
    ): Dependent {
        $this->mutationPolicy->enforce(MutationCapabilityCatalog::EMPLOYEE_DEPENDENT_ADD, [
            'employeeId' => $employeeId->value,
        ]);
        $this->employeeMutationAuth->assertAddDependent();

        if ($this->employees->findById($employeeId) === null) {
            throw new EmployeeNotFoundException('Employee not found.');
        }

        $validatedNationalCode = $nationalCode !== null && $nationalCode !== ''
            ? NationalCode::fromString($nationalCode)
            : null;

        return DB::transaction(function () use (
            $employeeId,
            $firstName,
            $lastName,
            $relationship,
            $age,
            $validatedNationalCode,
        ): Dependent {
            $dependent = Dependent::createNew(
                employeeId: $employeeId,
                firstName: $firstName,
                lastName: $lastName,
                relationship: $relationship,
                age: $age,
                nationalCode: $validatedNationalCode,
            );

            return $this->dependents->save($dependent);
        });
    }
}
