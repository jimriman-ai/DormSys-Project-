<?php

declare(strict_types=1);

use App\Modules\Employee\Application\Contracts\DependentRepositoryContract;
use App\Modules\Employee\Domain\Enums\DependentRelationship;
use App\Modules\Employee\Domain\Exceptions\DependentOwnershipException;
use App\Modules\Employee\Domain\Exceptions\EmployeeNotFoundException;
use App\Modules\Employee\Domain\ValueObjects\EmployeeId;
use App\Modules\Employee\Domain\ValueObjects\IdentityUserId;
use App\Support\Exceptions\ValidationException;
use App\Support\ValueObjects\Identity\NationalCode;

function createDependentTestIdentityUser(string $name = 'Dependent Test User', ?string $email = null): IdentityUserId
{
    $email ??= strtolower(str_replace(' ', '.', $name)).'.'.uniqid('', true).'@example.com';
    $user = createIdentityUserThroughMutation($name, $email);

    return IdentityUserId::fromString($user->requireId()->value);
}

function createDependentTestEmployee(IdentityUserId $identityId, string $code = 'DEP-EMP'): EmployeeId
{
    $employee = createEmployeeThroughMutation(
        identityId: $identityId,
        employeeCode: $code,
        firstName: 'Parent',
        lastName: 'Employee',
        nationalCode: NationalCode::fromString('0499370899'),
        hireDate: new DateTimeImmutable('2024-01-01'),
    );

    return $employee->requireId();
}

it('adds a dependent owned by the employee', function (): void {
    $identityId = createDependentTestIdentityUser('Add Dependent User');
    $employeeId = createDependentTestEmployee($identityId, 'EMP-DEP-01');

    $dependent = addDependentThroughMutation(
        employeeId: $employeeId,
        firstName: 'Sara',
        lastName: 'Employee',
        relationship: DependentRelationship::Spouse,
        age: 30,
        nationalCode: '0013542419',
    );

    expect($dependent->id)->not->toBeNull();
    expect($dependent->employeeId->value)->toBe($employeeId->value);
    expect($dependent->firstName)->toBe('Sara');
    expect($dependent->relationship)->toBe(DependentRelationship::Spouse);
    expect($dependent->nationalCode?->value)->toBe('0013542419');

    $listed = app(DependentRepositoryContract::class)->listByEmployeeId($employeeId);

    expect($listed)->toHaveCount(1);
    expect($listed[0]->requireId()->value)->toBe($dependent->requireId()->value);
});

it('updates a dependent for the owning employee', function (): void {
    $identityId = createDependentTestIdentityUser('Update Dependent User');
    $employeeId = createDependentTestEmployee($identityId, 'EMP-DEP-02');

    $dependent = addDependentThroughMutation(
        employeeId: $employeeId,
        firstName: 'Ali',
        lastName: 'Child',
        relationship: DependentRelationship::Child,
        age: 8,
    );

    $updated = updateDependentThroughMutation(
        employeeId: $employeeId,
        dependentId: $dependent->requireId(),
        firstName: 'Ali Reza',
        lastName: 'Child',
        relationship: DependentRelationship::Child,
        age: 9,
        nationalCode: '0013542419',
    );

    expect($updated->firstName)->toBe('Ali Reza');
    expect($updated->age)->toBe(9);
    expect($updated->nationalCode?->value)->toBe('0013542419');
    expect($updated->employeeId->value)->toBe($employeeId->value);
});

it('rejects adding a dependent without a parent employee', function (): void {
    addDependentThroughMutation(
        employeeId: EmployeeId::fromString('01900000-0000-7000-8000-000000000099'),
        firstName: 'Orphan',
        lastName: 'Dependent',
        relationship: DependentRelationship::Child,
    );
})->throws(EmployeeNotFoundException::class);

it('rejects updating a dependent owned by another employee', function (): void {
    $ownerIdentity = createDependentTestIdentityUser('Owner Employee');
    $otherIdentity = createDependentTestIdentityUser('Other Employee');

    $ownerId = createDependentTestEmployee($ownerIdentity, 'EMP-DEP-03');
    $otherId = createEmployeeThroughMutation(
        identityId: $otherIdentity,
        employeeCode: 'EMP-DEP-04',
        firstName: 'Other',
        lastName: 'Employee',
        nationalCode: NationalCode::fromString('0013542419'),
        hireDate: new DateTimeImmutable('2024-01-01'),
    )->requireId();

    $dependent = addDependentThroughMutation(
        employeeId: $ownerId,
        firstName: 'Mina',
        lastName: 'Child',
        relationship: DependentRelationship::Child,
    );

    updateDependentThroughMutation(
        employeeId: $otherId,
        dependentId: $dependent->requireId(),
        firstName: 'Mina',
        lastName: 'Changed',
        relationship: DependentRelationship::Child,
    );
})->throws(DependentOwnershipException::class);

it('rejects an invalid national code when provided', function (): void {
    $identityId = createDependentTestIdentityUser('Invalid National Code User');
    $employeeId = createDependentTestEmployee($identityId, 'EMP-DEP-05');

    addDependentThroughMutation(
        employeeId: $employeeId,
        firstName: 'Invalid',
        lastName: 'Code',
        relationship: DependentRelationship::Parent,
        nationalCode: '1234567890',
    );
})->throws(ValidationException::class);
