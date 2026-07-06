<?php

declare(strict_types=1);

use App\Modules\Employee\Application\Contracts\DepartmentRepositoryContract;
use App\Modules\Employee\Application\Contracts\EmployeeRepositoryContract;
use App\Modules\Employee\Domain\Exceptions\InactiveDepartmentAssignmentException;
use App\Modules\Employee\Domain\ValueObjects\EmployeeId;
use App\Modules\Employee\Domain\ValueObjects\IdentityUserId;
use App\Support\ValueObjects\Identity\NationalCode;

function createDepartmentTestIdentityUser(string $name = 'Dept Test User', ?string $email = null): IdentityUserId
{
    $email ??= strtolower(str_replace(' ', '.', $name)).'@example.com';
    $user = createIdentityUserThroughMutation($name, $email);

    return IdentityUserId::fromString($user->requireId()->value);
}

function createDepartmentTestEmployee(IdentityUserId $identityId, string $code = 'DEPT-EMP'): EmployeeId
{
    $employee = createEmployeeThroughMutation(
        identityId: $identityId,
        employeeCode: $code,
        firstName: 'Dept',
        lastName: 'Employee',
        nationalCode: NationalCode::fromString('0499370899'),
        hireDate: new DateTimeImmutable('2024-01-01'),
    );

    return $employee->requireId();
}

it('creates a department and persists it', function (): void {
    $department = createDepartmentThroughMutation(
        name: 'Human Resources',
        code: 'HR-001',
        lotteryPriority: 5,
    );

    expect($department->id)->not->toBeNull();
    expect($department->name)->toBe('Human Resources');
    expect($department->code)->toBe('HR-001');
    expect($department->lotteryPriority)->toBe(5);
    expect($department->isActive())->toBeTrue();

    $found = app(DepartmentRepositoryContract::class)->findById($department->requireId());

    expect($found)->not->toBeNull();
    expect($found?->code)->toBe('HR-001');
});

it('assigns an employee to a department and reloads departmentId', function (): void {
    $identityId = createDepartmentTestIdentityUser('Assign User');
    $employeeId = createDepartmentTestEmployee($identityId, 'EMP-DEPT-01');

    $department = createDepartmentThroughMutation(
        name: 'Operations',
        code: 'OPS-001',
    );

    $assigned = assignDepartmentToEmployeeThroughMutation(
        employeeId: $employeeId,
        departmentId: $department->requireId(),
    );

    expect($assigned->departmentId?->value)->toBe($department->requireId()->value);

    $reloaded = app(EmployeeRepositoryContract::class)->findById($employeeId);

    expect($reloaded)->not->toBeNull();
    expect($reloaded?->departmentId?->value)->toBe($department->requireId()->value);
});

it('rejects assignment to an inactive department', function (): void {
    $identityId = createDepartmentTestIdentityUser('Inactive Dept User');
    $employeeId = createDepartmentTestEmployee($identityId, 'EMP-DEPT-02');

    $department = createDepartmentThroughMutation(
        name: 'Legacy Unit',
        code: 'LEG-001',
    );

    deactivateDepartmentThroughMutation($department->requireId());

    assignDepartmentToEmployeeThroughMutation(
        employeeId: $employeeId,
        departmentId: $department->requireId(),
    );
})->throws(InactiveDepartmentAssignmentException::class);

it('deactivates a department while keeping existing employee assignments', function (): void {
    $identityId = createDepartmentTestIdentityUser('Deactivate User');
    $employeeId = createDepartmentTestEmployee($identityId, 'EMP-DEPT-03');

    $department = createDepartmentThroughMutation(
        name: 'Finance',
        code: 'FIN-001',
    );

    assignDepartmentToEmployeeThroughMutation(
        employeeId: $employeeId,
        departmentId: $department->requireId(),
    );

    $deactivated = deactivateDepartmentThroughMutation($department->requireId());

    expect($deactivated->isActive())->toBeFalse();

    $employee = app(EmployeeRepositoryContract::class)->findById($employeeId);

    expect($employee?->departmentId?->value)->toBe($department->requireId()->value);
});
