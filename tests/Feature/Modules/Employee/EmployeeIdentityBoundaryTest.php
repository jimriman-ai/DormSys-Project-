<?php

declare(strict_types=1);

use App\Modules\Employee\Application\Contracts\EmployeeRepositoryContract;
use App\Modules\Employee\Application\Services\CreateEmployeeAction;
use App\Modules\Employee\Domain\Entities\Employee;
use App\Modules\Employee\Domain\Exceptions\IdentityIdImmutableException;
use App\Modules\Employee\Domain\Exceptions\UnknownIdentityUserException;
use App\Modules\Employee\Domain\ValueObjects\IdentityUserId;
use App\Modules\Employee\Infrastructure\Persistence\Models\EmployeeModel;
use App\Modules\Identity\Application\Services\CreateUserAction;
use App\Modules\Identity\Application\Services\DeactivateUserAction;
use App\Modules\Identity\Domain\ValueObjects\UserId;
use App\Support\ValueObjects\Identity\NationalCode;
use Ramsey\Uuid\Uuid;

function createIdentityUser(string $name = 'Boundary User', ?string $email = null): IdentityUserId
{
    $email ??= strtolower(str_replace(' ', '.', $name)).'@example.com';
    $user = app(CreateUserAction::class)->execute($name, $email);

    return IdentityUserId::fromString($user->requireId()->value);
}

function createEmployeeForIdentity(IdentityUserId $identityId, string $code = 'EMP001'): Employee
{
    return app(CreateEmployeeAction::class)->execute(
        identityId: $identityId,
        employeeCode: $code,
        firstName: 'Ali',
        lastName: 'Rezaei',
        nationalCode: NationalCode::fromString('0499370899'),
        hireDate: new DateTimeImmutable('2024-01-01'),
    );
}

it('BT-01 creates employee with valid identity_id and keeps it immutable on reload', function (): void {
    $identityId = createIdentityUser('BT01 User');
    $employee = createEmployeeForIdentity($identityId);

    $loaded = app(EmployeeRepositoryContract::class)->findById($employee->requireId());

    expect($loaded)->not->toBeNull();
    assert($loaded instanceof Employee);
    expect($loaded->identityId->value)->toBe($identityId->value);

    $model = EmployeeModel::query()->find($employee->requireId()->value);

    expect($model?->identity_id)->toBe($identityId->value);
});

it('BT-02 rejects identity_id mutation after assignment', function (): void {
    $identityId = createIdentityUser('BT02 User');
    $employee = createEmployeeForIdentity($identityId, 'EMP002');

    $model = EmployeeModel::query()->findOrFail($employee->requireId()->value);
    $model->identity_id = Uuid::uuid7()->toString();

    expect(fn () => $model->save())->toThrow(IdentityIdImmutableException::class);
});

it('BT-03 rejects employee create when identity user is unknown', function (): void {
    $unknownId = IdentityUserId::fromString(Uuid::uuid7()->toString());

    expect(fn () => createEmployeeForIdentity($unknownId, 'EMP003'))
        ->toThrow(UnknownIdentityUserException::class);

    expect(EmployeeModel::query()->count())->toBe(0);
});

it('OA-03-02 allows create when identity user is disabled', function (): void {
    $identityId = createIdentityUser('Disabled Identity User', 'disabled.identity@example.com');

    app(DeactivateUserAction::class)->execute(UserId::fromString($identityId->value));

    $employee = createEmployeeForIdentity($identityId, 'EMP004');

    expect($employee->identityId->value)->toBe($identityId->value);
});

it('BT-04 keeps employee unchanged when identity user is deactivated after create', function (): void {
    $identityId = createIdentityUser('BT04 User', 'bt04@example.com');
    $employee = createEmployeeForIdentity($identityId, 'EMP005');

    app(DeactivateUserAction::class)->execute(UserId::fromString($identityId->value));

    $reloaded = app(EmployeeRepositoryContract::class)->findById($employee->requireId());

    expect($reloaded)->not->toBeNull();
    assert($reloaded instanceof Employee);
    expect($reloaded->isActive())->toBeTrue()
        ->and($reloaded->identityId->value)->toBe($identityId->value);
});
