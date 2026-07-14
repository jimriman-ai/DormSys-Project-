<?php

declare(strict_types=1);

use App\Application\Mutation\Support\MutationPrincipalContext;
use App\Modules\Employee\Application\Services\AddDependentAction;
use App\Modules\Employee\Application\Services\AssignDepartmentToEmployeeAction;
use App\Modules\Employee\Application\Services\CreateDepartmentAction;
use App\Modules\Employee\Application\Services\CreateEmployeeAction;
use App\Modules\Employee\Application\Services\DeactivateDepartmentAction;
use App\Modules\Employee\Application\Services\UpdateDependentAction;
use App\Modules\Employee\Domain\Entities\Department;
use App\Modules\Employee\Domain\Entities\Dependent;
use App\Modules\Employee\Domain\Entities\Employee;
use App\Modules\Employee\Domain\Enums\DependentRelationship;
use App\Modules\Employee\Domain\ValueObjects\DepartmentId;
use App\Modules\Employee\Domain\ValueObjects\DependentId;
use App\Modules\Employee\Domain\ValueObjects\EmployeeId;
use App\Modules\Employee\Domain\ValueObjects\IdentityUserId;
use App\Modules\Identity\Application\Services\AssignRoleToUserAction;
use App\Modules\Identity\Application\Services\CreateUserAction;
use App\Modules\Identity\Application\Services\DeactivateUserAction;
use App\Modules\Identity\Application\Services\RevokeRoleFromUserAction;
use App\Modules\Identity\Domain\Entities\User;
use App\Modules\Identity\Domain\ValueObjects\UserId;
use App\Modules\Identity\Infrastructure\Persistence\Models\UserModel;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use App\Support\ValueObjects\Identity\NationalCode;
use Database\Seeders\IdentityRoleSeeder;
use Spatie\Permission\Models\Permission;

/**
 * @template TReturn
 *
 * @param  callable(): TReturn  $callback
 * @return TReturn
 */
function mutationActingAs(string $principalId, callable $callback): mixed
{
    return MutationPrincipalContext::runAs($principalId, $callback);
}

function mutationBootstrapPrincipalId(): string
{
    return UuidGenerator::uuid7();
}

function createIdentityUserThroughMutation(string $displayName, ?string $email = null): User
{
    return mutationActingAs(
        mutationBootstrapPrincipalId(),
        fn () => app(CreateUserAction::class)->execute($displayName, $email),
    );
}

function createActiveMutationActorId(string $displayName = 'Mutation Actor', ?string $email = null): string
{
    $email ??= 'mutation.actor.'.uniqid('', true).'@example.com';

    return createIdentityUserThroughMutation($displayName, $email)->requireId()->value;
}

/**
 * Test-only direct grant of the role-assign actor permission (D-L7-1).
 * Not a production role→permission mapping decision.
 */
function grantIdentityRolesManagePermission(string $userId): void
{
    Permission::findOrCreate(IdentityRoleSeeder::PERMISSION_IDENTITY_ROLES_MANAGE, 'web');

    $model = UserModel::query()->findOrFail($userId);

    if (! $model->checkPermissionTo(IdentityRoleSeeder::PERMISSION_IDENTITY_ROLES_MANAGE)) {
        $model->givePermissionTo(IdentityRoleSeeder::PERMISSION_IDENTITY_ROLES_MANAGE);
    }
}

/**
 * @template TReturn
 *
 * @param  callable(): TReturn  $callback
 * @return TReturn
 */
function withMutationActor(callable $callback, ?string $actorId = null): mixed
{
    return mutationActingAs($actorId ?? createActiveMutationActorId(), $callback);
}

function assignRoleThroughMutation(UserId $userId, string $roleName, ?string $actorId = null): void
{
    $resolvedActorId = $actorId ?? createActiveMutationActorId();
    grantIdentityRolesManagePermission($resolvedActorId);

    mutationActingAs(
        $resolvedActorId,
        fn () => app(AssignRoleToUserAction::class)->execute($userId, $roleName),
    );
}

function revokeRoleFromUserThroughMutation(UserId $userId, string $roleName, ?string $actorId = null): void
{
    withMutationActor(
        fn () => app(RevokeRoleFromUserAction::class)->execute($userId, $roleName),
        $actorId,
    );
}

function deactivateUserThroughMutation(UserId $userId, ?string $actorId = null): User
{
    return withMutationActor(
        fn () => app(DeactivateUserAction::class)->execute($userId),
        $actorId,
    );
}

function createEmployeeThroughMutation(
    IdentityUserId $identityId,
    string $employeeCode,
    string $firstName,
    string $lastName,
    NationalCode $nationalCode,
    DateTimeImmutable $hireDate,
    ?string $actorId = null,
): Employee {
    return withMutationActor(
        fn () => app(CreateEmployeeAction::class)->execute(
            identityId: $identityId,
            employeeCode: $employeeCode,
            firstName: $firstName,
            lastName: $lastName,
            nationalCode: $nationalCode,
            hireDate: $hireDate,
        ),
        $actorId,
    );
}

function createDepartmentThroughMutation(
    string $name,
    string $code,
    int $lotteryPriority = 0,
    ?string $actorId = null,
): Department {
    return withMutationActor(
        fn () => app(CreateDepartmentAction::class)->execute(
            name: $name,
            code: $code,
            lotteryPriority: $lotteryPriority,
        ),
        $actorId,
    );
}

function assignDepartmentToEmployeeThroughMutation(
    EmployeeId $employeeId,
    DepartmentId $departmentId,
    ?string $actorId = null,
): Employee {
    return withMutationActor(
        fn () => app(AssignDepartmentToEmployeeAction::class)->execute(
            employeeId: $employeeId,
            departmentId: $departmentId,
        ),
        $actorId,
    );
}

function deactivateDepartmentThroughMutation(DepartmentId $departmentId, ?string $actorId = null): Department
{
    return withMutationActor(
        fn () => app(DeactivateDepartmentAction::class)->execute($departmentId),
        $actorId,
    );
}

function addDependentThroughMutation(
    EmployeeId $employeeId,
    string $firstName,
    string $lastName,
    DependentRelationship $relationship,
    ?int $age = null,
    ?string $nationalCode = null,
    ?string $actorId = null,
): Dependent {
    return withMutationActor(
        fn () => app(AddDependentAction::class)->execute(
            employeeId: $employeeId,
            firstName: $firstName,
            lastName: $lastName,
            relationship: $relationship,
            age: $age,
            nationalCode: $nationalCode,
        ),
        $actorId,
    );
}

function updateDependentThroughMutation(
    EmployeeId $employeeId,
    DependentId $dependentId,
    string $firstName,
    string $lastName,
    DependentRelationship $relationship,
    ?int $age = null,
    ?string $nationalCode = null,
    ?string $actorId = null,
): Dependent {
    return withMutationActor(
        fn () => app(UpdateDependentAction::class)->execute(
            employeeId: $employeeId,
            dependentId: $dependentId,
            firstName: $firstName,
            lastName: $lastName,
            relationship: $relationship,
            age: $age,
            nationalCode: $nationalCode,
        ),
        $actorId,
    );
}

/**
 * @template TReturn
 *
 * @param  callable(): TReturn  $callback
 * @return TReturn
 */
function withLotteryMutationActor(callable $callback, ?string $actorId = null): mixed
{
    return withMutationActor($callback, $actorId);
}

/**
 * @template TReturn
 *
 * @param  callable(): TReturn  $callback
 * @return TReturn
 */
function withAllocationMutationActor(callable $callback, ?string $actorId = null): mixed
{
    return withMutationActor($callback, $actorId);
}

/**
 * @template TReturn
 *
 * @param  callable(): TReturn  $callback
 * @return TReturn
 */
function asLotterySystemMutation(callable $callback): mixed
{
    return MutationPrincipalContext::runAsSystem($callback);
}
