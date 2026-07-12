<?php

declare(strict_types=1);

use App\Modules\Employee\Application\Contracts\EmployeeEligibilityContract;
use App\Modules\Employee\Application\Contracts\EmployeeRepositoryContract;
use App\Modules\Employee\Application\Contracts\Ports\ActiveAllocationReadPort;
use App\Modules\Employee\Application\Contracts\Ports\PendingRequestReadPort;
use App\Modules\Employee\Domain\Entities\Employee;
use App\Modules\Employee\Domain\Enums\EligibilityReasonCode;
use App\Modules\Employee\Domain\ValueObjects\EmployeeId;
use App\Modules\Employee\Domain\ValueObjects\IdentityUserId;
use App\Support\ValueObjects\Identity\NationalCode;

function createEmployeeForEligibilityContractTest(string $suffix = ''): Employee
{
    $token = $suffix !== '' ? $suffix : substr(uniqid('', true), -6);
    $user = createIdentityUserThroughMutation(
        'Eligibility Contract User '.$token,
        'eligibility.contract.'.$token.'@example.com',
    );

    return createEmployeeThroughMutation(
        identityId: IdentityUserId::fromString($user->requireId()->value),
        employeeCode: 'EMP-ELIG-'.$token,
        firstName: 'Eligible',
        lastName: 'Employee',
        nationalCode: NationalCode::fromString('0499370899'),
        hireDate: new DateTimeImmutable('2024-01-01'),
    );
}

it('returns eligible for an active employee with null allocation and no pending request', function (): void {
    $employee = createEmployeeForEligibilityContractTest('active');

    $result = app(EmployeeEligibilityContract::class)->computeRequestEligibility(
        $employee->requireId()->value,
    );

    expect($result->eligible)->toBeTrue();
    expect($result->reasonCodes)->toBe([]);
});

it('returns employee_inactive for an inactive employee', function (): void {
    $employee = createEmployeeForEligibilityContractTest('inactive');
    $employee->deactivate();
    app(EmployeeRepositoryContract::class)->save($employee);

    $result = app(EmployeeEligibilityContract::class)->computeRequestEligibility(
        $employee->requireId()->value,
    );

    expect($result->eligible)->toBeFalse();
    expect($result->reasonCodes)->toBe([EligibilityReasonCode::EmployeeInactive->value]);
});

it('returns active_allocation_exists when ActiveAllocationReadPort reports true', function (): void {
    $employee = createEmployeeForEligibilityContractTest('alloc');

    $this->app->instance(ActiveAllocationReadPort::class, new class implements ActiveAllocationReadPort
    {
        public function hasActiveAllocation(EmployeeId $employeeId): bool
        {
            return true;
        }
    });

    $result = app(EmployeeEligibilityContract::class)->computeRequestEligibility(
        $employee->requireId()->value,
    );

    expect($result->eligible)->toBeFalse();
    expect($result->reasonCodes)->toBe([EligibilityReasonCode::ActiveAllocationExists->value]);
});

it('returns pending_request_exists when PendingRequestReadPort reports true', function (): void {
    $employee = createEmployeeForEligibilityContractTest('pending');

    $this->app->instance(PendingRequestReadPort::class, new class implements PendingRequestReadPort
    {
        public function hasPendingRequest(string $employeeId, ?string $excludingRequestId = null): bool
        {
            return true;
        }
    });

    $result = app(EmployeeEligibilityContract::class)->computeRequestEligibility(
        $employee->requireId()->value,
    );

    expect($result->eligible)->toBeFalse();
    expect($result->reasonCodes)->toBe([EligibilityReasonCode::PendingRequestExists->value]);
});
