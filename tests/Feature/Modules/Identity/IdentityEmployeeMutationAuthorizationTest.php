<?php

declare(strict_types=1);

use App\Application\Mutation\Exceptions\UnauthorizedMutationException;
use App\Application\Mutation\Registry\MutationCapabilityCatalog;
use App\Application\Mutation\Registry\PendingMutationAuthorizationRegistry;
use App\Application\Mutation\Support\MutationPrincipalContextHolder;
use App\Modules\Employee\Application\Contracts\DepartmentRepositoryContract;
use App\Modules\Employee\Application\Contracts\EmployeeRepositoryContract;
use App\Modules\Employee\Application\Services\AssignDepartmentToEmployeeAction;
use App\Modules\Employee\Application\Services\CreateDepartmentAction;
use App\Modules\Employee\Application\Services\CreateEmployeeAction;
use App\Modules\Employee\Application\Services\DeactivateDepartmentAction;
use App\Modules\Employee\Domain\ValueObjects\IdentityUserId;
use App\Modules\Identity\Application\Contracts\IdentityUserReadContract;
use App\Modules\Identity\Application\Contracts\UserRepositoryContract;
use App\Modules\Identity\Application\Services\AssignRoleToUserAction;
use App\Modules\Identity\Application\Services\CreateUserAction;
use App\Modules\Identity\Application\Services\DeactivateUserAction;
use App\Modules\Identity\Application\Services\RevokeRoleFromUserAction;
use App\Modules\Identity\Domain\Enums\UserStatus;
use App\Modules\Identity\Domain\ValueObjects\UserId;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use App\Support\ValueObjects\Identity\NationalCode;
use Database\Seeders\IdentityRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    app(MutationPrincipalContextHolder::class)->clear();
    request()->attributes->remove('audit_principal_user_id');
});

function createIdentityEmployeeMutationActor(): string
{
    return createActiveMutationActorId('Identity Employee Actor');
}

it('denies identity user create without a mutation principal', function (): void {
    expect(fn () => app(CreateUserAction::class)->execute('Denied User', 'denied@example.com'))
        ->toThrow(UnauthorizedMutationException::class);

    expect(app(UserRepositoryContract::class)->existsByEmail('denied@example.com'))->toBeFalse();
});

it('allows identity user create with a bootstrap principal', function (): void {
    $user = mutationActingAs(
        mutationBootstrapPrincipalId(),
        fn () => app(CreateUserAction::class)->execute('Allowed User', 'allowed@example.com'),
    );

    expect($user->isActive())->toBeTrue();
});

it('denies identity user deactivate without a mutation principal', function (): void {
    $target = createIdentityUserThroughMutation('Deactivate Target', 'deactivate-target@example.com');

    expect(fn () => app(DeactivateUserAction::class)->execute($target->requireId()))
        ->toThrow(UnauthorizedMutationException::class);

    expect(app(UserRepositoryContract::class)->findById($target->requireId())?->isActive())->toBeTrue();
});

it('denies identity user deactivate when principal is inactive', function (): void {
    $actorId = createIdentityEmployeeMutationActor();
    $target = createIdentityUserThroughMutation('Inactive Actor Target', 'inactive-actor-target@example.com');

    deactivateUserThroughMutation(
        UserId::fromString($actorId),
        createActiveMutationActorId('Second Actor'),
    );

    expect(fn () => mutationActingAs($actorId, fn () => app(DeactivateUserAction::class)->execute($target->requireId())))
        ->toThrow(UnauthorizedMutationException::class, 'Mutation actor must be an active identity user.');

    expect(app(UserRepositoryContract::class)->findById($target->requireId())?->isActive())->toBeTrue();
});

it('allows identity user deactivate for an active mutation actor', function (): void {
    $actorId = createIdentityEmployeeMutationActor();
    $target = createIdentityUserThroughMutation('Allowed Deactivate Target', 'allowed-deactivate@example.com');

    $deactivated = mutationActingAs($actorId, fn () => app(DeactivateUserAction::class)->execute($target->requireId()));

    expect($deactivated->status)->toBe(UserStatus::Disabled);
});

it('denies role assign without a mutation principal', function (): void {
    Artisan::call('db:seed', ['--class' => IdentityRoleSeeder::class]);
    $target = createIdentityUserThroughMutation('Role Target', 'role-target@example.com');

    expect(fn () => app(AssignRoleToUserAction::class)->execute(
        $target->requireId(),
        IdentityRoleSeeder::ROLE_ADMINISTRATOR,
    ))->toThrow(UnauthorizedMutationException::class);
});

it('allows role assign for an active mutation actor', function (): void {
    Artisan::call('db:seed', ['--class' => IdentityRoleSeeder::class]);
    $actorId = createIdentityEmployeeMutationActor();
    $target = createIdentityUserThroughMutation('Role Assign Target', 'role-assign@example.com');

    mutationActingAs($actorId, fn () => app(AssignRoleToUserAction::class)->execute(
        $target->requireId(),
        IdentityRoleSeeder::ROLE_ADMINISTRATOR,
    ));

    expect(app(IdentityUserReadContract::class)->userHasRole($target->requireId()->value, IdentityRoleSeeder::ROLE_ADMINISTRATOR))->toBeTrue();
});

it('denies role revoke without a mutation principal', function (): void {
    Artisan::call('db:seed', ['--class' => IdentityRoleSeeder::class]);
    $actorId = createIdentityEmployeeMutationActor();
    $target = createIdentityUserThroughMutation('Revoke Target', 'revoke-target@example.com');

    mutationActingAs($actorId, fn () => app(AssignRoleToUserAction::class)->execute(
        $target->requireId(),
        IdentityRoleSeeder::ROLE_ADMINISTRATOR,
    ));

    expect(fn () => app(RevokeRoleFromUserAction::class)->execute(
        $target->requireId(),
        IdentityRoleSeeder::ROLE_ADMINISTRATOR,
    ))->toThrow(UnauthorizedMutationException::class);
});

it('allows role revoke for an active mutation actor', function (): void {
    Artisan::call('db:seed', ['--class' => IdentityRoleSeeder::class]);
    $actorId = createIdentityEmployeeMutationActor();
    $target = createIdentityUserThroughMutation('Allowed Revoke Target', 'allowed-revoke@example.com');

    mutationActingAs($actorId, fn () => app(AssignRoleToUserAction::class)->execute(
        $target->requireId(),
        IdentityRoleSeeder::ROLE_ADMINISTRATOR,
    ));

    mutationActingAs($actorId, fn () => app(RevokeRoleFromUserAction::class)->execute(
        $target->requireId(),
        IdentityRoleSeeder::ROLE_ADMINISTRATOR,
    ));

    expect(app(IdentityUserReadContract::class)->userHasRole($target->requireId()->value, IdentityRoleSeeder::ROLE_ADMINISTRATOR))->toBeFalse();
});

it('denies employee create without a mutation principal', function (): void {
    $identityId = createIdentityUserThroughMutation('Employee Identity', 'employee-identity@example.com')->requireId()->value;

    expect(fn () => app(CreateEmployeeAction::class)->execute(
        identityId: IdentityUserId::fromString($identityId),
        employeeCode: 'EMP-DENIED',
        firstName: 'Denied',
        lastName: 'Employee',
        nationalCode: NationalCode::fromString('0499370899'),
        hireDate: new DateTimeImmutable('2024-01-01'),
    ))->toThrow(UnauthorizedMutationException::class);

    expect(app(EmployeeRepositoryContract::class)->findByIdentityId(IdentityUserId::fromString($identityId)))->toBeNull();
});

it('allows employee create for an active mutation actor', function (): void {
    $identityId = createIdentityUserThroughMutation('Employee Allowed Identity', 'employee-allowed@example.com')->requireId()->value;
    $actorId = createIdentityEmployeeMutationActor();

    $employee = mutationActingAs($actorId, fn () => app(CreateEmployeeAction::class)->execute(
        identityId: IdentityUserId::fromString($identityId),
        employeeCode: 'EMP-ALLOWED',
        firstName: 'Allowed',
        lastName: 'Employee',
        nationalCode: NationalCode::fromString('0000000019'),
        hireDate: new DateTimeImmutable('2024-01-01'),
    ));

    expect($employee->requireId()->value)->not->toBeEmpty();
});

it('denies department create without a mutation principal', function (): void {
    expect(fn () => app(CreateDepartmentAction::class)->execute(
        name: 'Denied Department',
        code: 'DEN-001',
    ))->toThrow(UnauthorizedMutationException::class);
});

it('allows department create for an active mutation actor', function (): void {
    $actorId = createIdentityEmployeeMutationActor();

    $department = mutationActingAs($actorId, fn () => app(CreateDepartmentAction::class)->execute(
        name: 'Allowed Department',
        code: 'ALL-001',
    ));

    expect($department->isActive())->toBeTrue();
});

it('denies department deactivate without a mutation principal', function (): void {
    $department = createDepartmentThroughMutation('Deactivate Dept', 'DEA-001');

    expect(fn () => app(DeactivateDepartmentAction::class)->execute($department->requireId()))
        ->toThrow(UnauthorizedMutationException::class);

    expect(app(DepartmentRepositoryContract::class)->findById($department->requireId())?->isActive())->toBeTrue();
});

it('allows department deactivate for an active mutation actor', function (): void {
    $actorId = createIdentityEmployeeMutationActor();
    $department = createDepartmentThroughMutation('Allowed Deactivate Dept', 'ADE-001');

    $deactivated = mutationActingAs($actorId, fn () => app(DeactivateDepartmentAction::class)->execute($department->requireId()));

    expect($deactivated->isActive())->toBeFalse();
});

it('denies department assignment without a mutation principal', function (): void {
    $identityId = IdentityUserId::fromString(
        createIdentityUserThroughMutation('Assign Identity', 'assign-identity@example.com')->requireId()->value,
    );
    $employeeId = createEmployeeThroughMutation(
        identityId: $identityId,
        employeeCode: 'EMP-ASSIGN-DENIED',
        firstName: 'Assign',
        lastName: 'Denied',
        nationalCode: NationalCode::fromString('0499370899'),
        hireDate: new DateTimeImmutable('2024-01-01'),
    )->requireId();
    $department = createDepartmentThroughMutation('Assign Dept', 'ASG-001');

    expect(fn () => app(AssignDepartmentToEmployeeAction::class)->execute(
        employeeId: $employeeId,
        departmentId: $department->requireId(),
    ))->toThrow(UnauthorizedMutationException::class);

    expect(app(EmployeeRepositoryContract::class)->findById($employeeId)?->departmentId)->toBeNull();
});

it('allows department assignment for an active mutation actor', function (): void {
    $actorId = createIdentityEmployeeMutationActor();
    $identityId = IdentityUserId::fromString(
        createIdentityUserThroughMutation('Assign Allowed Identity', 'assign-allowed@example.com')->requireId()->value,
    );
    $employeeId = createEmployeeThroughMutation(
        identityId: $identityId,
        employeeCode: 'EMP-ASSIGN-ALLOWED',
        firstName: 'Assign',
        lastName: 'Allowed',
        nationalCode: NationalCode::fromString('0000000019'),
        hireDate: new DateTimeImmutable('2024-01-01'),
    )->requireId();
    $department = createDepartmentThroughMutation('Assign Allowed Dept', 'ASA-001');

    $assigned = mutationActingAs($actorId, fn () => app(AssignDepartmentToEmployeeAction::class)->execute(
        employeeId: $employeeId,
        departmentId: $department->requireId(),
    ));

    expect($assigned->departmentId?->value)->toBe($department->requireId()->value);
});

it('fails closed when principal context is missing for employee create', function (): void {
    $identityId = createIdentityUserThroughMutation('Missing Principal Identity', 'missing-principal@example.com')->requireId()->value;

    expect(fn () => app(CreateEmployeeAction::class)->execute(
        identityId: IdentityUserId::fromString($identityId),
        employeeCode: 'EMP-MISSING',
        firstName: 'Missing',
        lastName: 'Principal',
        nationalCode: NationalCode::fromString('0499370899'),
        hireDate: new DateTimeImmutable('2024-01-01'),
    ))->toThrow(UnauthorizedMutationException::class, 'Mutation requires an authorized principal.');
});

it('uses mutation principal holder over request audit principal', function (): void {
    $holderPrincipal = mutationBootstrapPrincipalId();
    $auditPrincipal = UuidGenerator::uuid7();
    request()->attributes->set('audit_principal_user_id', $auditPrincipal);

    $user = mutationActingAs($holderPrincipal, fn () => app(CreateUserAction::class)->execute(
        'Holder Precedence User',
        'holder-precedence@example.com',
    ));

    expect($user->requireId()->value)->not->toBe($auditPrincipal);
});

it('registers identity and employee actions as enforced rather than pending', function (): void {
    expect(PendingMutationAuthorizationRegistry::isPending(CreateUserAction::class))->toBeFalse()
        ->and(PendingMutationAuthorizationRegistry::isPending(DeactivateUserAction::class))->toBeFalse()
        ->and(PendingMutationAuthorizationRegistry::isPending(AssignRoleToUserAction::class))->toBeFalse()
        ->and(PendingMutationAuthorizationRegistry::isPending(RevokeRoleFromUserAction::class))->toBeFalse()
        ->and(PendingMutationAuthorizationRegistry::isPending(CreateEmployeeAction::class))->toBeFalse()
        ->and(PendingMutationAuthorizationRegistry::isPending(CreateDepartmentAction::class))->toBeFalse()
        ->and(PendingMutationAuthorizationRegistry::isPending(DeactivateDepartmentAction::class))->toBeFalse()
        ->and(PendingMutationAuthorizationRegistry::isPending(AssignDepartmentToEmployeeAction::class))->toBeFalse();
});

it('registers identity and employee mutation capability keys', function (): void {
    expect(MutationCapabilityCatalog::registeredKeys())->toContain(
        MutationCapabilityCatalog::IDENTITY_USER_CREATE,
        MutationCapabilityCatalog::IDENTITY_USER_DEACTIVATE,
        MutationCapabilityCatalog::IDENTITY_ROLE_ASSIGN,
        MutationCapabilityCatalog::IDENTITY_ROLE_REVOKE,
        MutationCapabilityCatalog::EMPLOYEE_CREATE,
        MutationCapabilityCatalog::EMPLOYEE_DEPARTMENT_CREATE,
        MutationCapabilityCatalog::EMPLOYEE_DEPARTMENT_DEACTIVATE,
        MutationCapabilityCatalog::EMPLOYEE_DEPARTMENT_ASSIGN,
    );
});
