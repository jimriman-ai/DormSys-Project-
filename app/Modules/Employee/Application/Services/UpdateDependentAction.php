<?php

declare(strict_types=1);

namespace App\Modules\Employee\Application\Services;

use App\Application\Mutation\Registry\MutationCapabilityCatalog;
use App\Application\Mutation\Services\MutationPolicyEnforcementPoint;
use App\Modules\Employee\Application\Contracts\DependentRepositoryContract;
use App\Modules\Employee\Domain\Entities\Dependent;
use App\Modules\Employee\Domain\Enums\DependentRelationship;
use App\Modules\Employee\Domain\Exceptions\DependentNotFoundException;
use App\Modules\Employee\Domain\Exceptions\DependentOwnershipException;
use App\Modules\Employee\Domain\ValueObjects\DependentId;
use App\Modules\Employee\Domain\ValueObjects\EmployeeId;
use App\Support\ValueObjects\Identity\NationalCode;
use Illuminate\Support\Facades\DB;

final class UpdateDependentAction
{
    public function __construct(
        private readonly DependentRepositoryContract $dependents,
        private readonly MutationPolicyEnforcementPoint $mutationPolicy,
        private readonly EmployeeMutationAuthorizationGate $employeeMutationAuth,
    ) {}

    public function execute(
        EmployeeId $employeeId,
        DependentId $dependentId,
        string $firstName,
        string $lastName,
        DependentRelationship $relationship,
        ?int $age = null,
        ?string $nationalCode = null,
    ): Dependent {
        $this->mutationPolicy->enforce(MutationCapabilityCatalog::EMPLOYEE_DEPENDENT_UPDATE, [
            'employeeId' => $employeeId->value,
            'dependentId' => $dependentId->value,
        ]);
        $this->employeeMutationAuth->assertUpdateDependent();

        $dependent = $this->dependents->findById($dependentId);

        if ($dependent === null) {
            throw new DependentNotFoundException('Dependent not found.');
        }

        if (! $dependent->belongsTo($employeeId)) {
            throw new DependentOwnershipException('Dependent does not belong to the given employee.');
        }

        $validatedNationalCode = $nationalCode !== null && $nationalCode !== ''
            ? NationalCode::fromString($nationalCode)
            : null;

        return DB::transaction(function () use (
            $dependent,
            $firstName,
            $lastName,
            $relationship,
            $age,
            $validatedNationalCode,
        ): Dependent {
            $dependent->updateDetails(
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
