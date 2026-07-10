<?php

declare(strict_types=1);

namespace App\Application\Development;

use App\Application\Mutation\Support\MutationPrincipalContext;
use App\Models\User;
use App\Modules\Employee\Application\Contracts\EmployeeRepositoryContract;
use App\Modules\Employee\Application\Services\CreateEmployeeAction;
use App\Modules\Employee\Domain\Entities\Employee;
use App\Modules\Employee\Domain\ValueObjects\IdentityUserId;
use App\Modules\Identity\Application\Contracts\UserRepositoryContract;
use App\Modules\Identity\Application\Services\AssignRoleToUserAction;
use App\Modules\Identity\Application\Services\CreateUserAction;
use App\Modules\Identity\Domain\Entities\User as IdentityUser;
use App\Modules\Identity\Domain\ValueObjects\UserId;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use App\Support\Development\DevelopmentUserAccountReport;
use App\Support\ValueObjects\Identity\NationalCode;
use DateTimeImmutable;

final class DevelopmentUserProvisioner
{
    public function __construct(
        private readonly CreateUserAction $createIdentityUserAction,
        private readonly CreateEmployeeAction $createEmployeeAction,
        private readonly AssignRoleToUserAction $assignRoleAction,
        private readonly UserRepositoryContract $identityUsers,
        private readonly EmployeeRepositoryContract $employees,
    ) {}

    /**
     * @param  list<array{
     *     label: string,
     *     display_name: string,
     *     email: string,
     *     password: string,
     *     roles: list<string>,
     *     employee?: array{
     *         code: string,
     *         first_name: string,
     *         last_name: string,
     *         national_code: string,
     *         hire_date: string,
     *     }|null
     * }>  $accounts
     * @return list<DevelopmentUserAccountReport>
     */
    public function provision(array $accounts): array
    {
        $reports = [];
        $mutationPrincipalId = $this->resolveMutationPrincipalId($accounts);

        foreach ($accounts as $account) {
            $reports[] = $this->provisionAccount($account, $mutationPrincipalId);
            $mutationPrincipalId = $this->resolveMutationPrincipalId($accounts);
        }

        return $reports;
    }

    /**
     * @param  array{
     *     label: string,
     *     display_name: string,
     *     email: string,
     *     password: string,
     *     roles: list<string>,
     *     employee?: array{
     *         code: string,
     *         first_name: string,
     *         last_name: string,
     *         national_code: string,
     *         hire_date: string,
     *     }|null
     * }  $account
     */
    private function provisionAccount(array $account, string $mutationPrincipalId): DevelopmentUserAccountReport
    {
        $credentialCreated = $this->ensureCredentialUser(
            displayName: $account['display_name'],
            email: $account['email'],
            password: $account['password'],
        );

        $existingIdentity = $this->identityUsers->findByEmail($account['email']);
        $identityCreated = $existingIdentity === null;

        $identityUser = $existingIdentity ?? $this->createIdentityUser(
            displayName: $account['display_name'],
            email: $account['email'],
            mutationPrincipalId: $mutationPrincipalId,
        );

        $identityId = $identityUser->requireId()->value;
        $employeeId = null;
        $employeeCreated = false;
        $employeePayload = $account['employee'] ?? null;

        if ($employeePayload !== null) {
            $identityReference = IdentityUserId::fromString($identityId);

            if ($this->employees->existsByIdentityId($identityReference)) {
                $employeeId = $this->employees->findEmployeeIdByIdentityUserId($identityId);
            } else {
                $employee = $this->createEmployee(
                    identityId: $identityReference,
                    employee: $employeePayload,
                    mutationPrincipalId: $mutationPrincipalId,
                );
                $employeeId = $employee->requireId()->value;
                $employeeCreated = true;
            }
        }

        $assignedRoles = [];

        foreach ($account['roles'] as $roleName) {
            if ($this->identityUsers->userHasRole(UserId::fromString($identityId), $roleName)) {
                $assignedRoles[] = $roleName;

                continue;
            }

            $this->assignRoleToUser(
                userId: UserId::fromString($identityId),
                roleName: $roleName,
                mutationPrincipalId: $mutationPrincipalId,
            );
            $assignedRoles[] = $roleName;
        }

        return new DevelopmentUserAccountReport(
            label: $account['label'],
            email: $account['email'],
            password: $account['password'],
            credentialCreated: $credentialCreated,
            identityId: $identityId,
            identityCreated: $identityCreated,
            employeeId: $employeeId,
            employeeCreated: $employeeCreated,
            roles: $assignedRoles,
        );
    }

    /**
     * @param  list<array{
     *     label: string,
     *     display_name: string,
     *     email: string,
     *     password: string,
     *     roles: list<string>,
     *     employee?: array{
     *         code: string,
     *         first_name: string,
     *         last_name: string,
     *         national_code: string,
     *         hire_date: string,
     *     }|null
     * }>  $accounts
     */
    private function resolveMutationPrincipalId(array $accounts): string
    {
        foreach ($accounts as $account) {
            $identityUser = $this->identityUsers->findByEmail($account['email']);

            if ($identityUser !== null) {
                return $identityUser->requireId()->value;
            }
        }

        return UuidGenerator::uuid7();
    }

    private function ensureCredentialUser(string $displayName, string $email, string $password): bool
    {
        if (User::query()->where('email', $email)->exists()) {
            return false;
        }

        User::factory()->create([
            'name' => $displayName,
            'email' => $email,
            'password' => $password,
        ]);

        return true;
    }

    private function createIdentityUser(
        string $displayName,
        string $email,
        string $mutationPrincipalId,
    ): IdentityUser {
        return MutationPrincipalContext::runAs(
            $mutationPrincipalId,
            fn (): IdentityUser => $this->createIdentityUserAction->execute($displayName, $email),
        );
    }

    /**
     * @param  array{
     *     code: string,
     *     first_name: string,
     *     last_name: string,
     *     national_code: string,
     *     hire_date: string,
     * }  $employee
     */
    private function createEmployee(
        IdentityUserId $identityId,
        array $employee,
        string $mutationPrincipalId,
    ): Employee {
        return MutationPrincipalContext::runAs(
            $mutationPrincipalId,
            fn () => $this->createEmployeeAction->execute(
                identityId: $identityId,
                employeeCode: $employee['code'],
                firstName: $employee['first_name'],
                lastName: $employee['last_name'],
                nationalCode: NationalCode::fromString($employee['national_code']),
                hireDate: new DateTimeImmutable($employee['hire_date']),
            ),
        );
    }

    private function assignRoleToUser(UserId $userId, string $roleName, string $mutationPrincipalId): void
    {
        if (! $this->isActiveIdentityPrincipal($mutationPrincipalId)) {
            $this->withEphemeralMutationActor(
                fn () => $this->assignRoleAction->execute($userId, $roleName),
            );

            return;
        }

        MutationPrincipalContext::runAs(
            $mutationPrincipalId,
            fn () => $this->assignRoleAction->execute($userId, $roleName),
        );
    }

    private function isActiveIdentityPrincipal(string $principalId): bool
    {
        $user = $this->identityUsers->findById(UserId::fromString($principalId));

        return $user !== null && $user->isActive();
    }

    /**
     * @template TReturn
     *
     * @param  callable(): TReturn  $callback
     * @return TReturn
     */
    private function withEphemeralMutationActor(callable $callback): mixed
    {
        $actorId = MutationPrincipalContext::runAs(
            UuidGenerator::uuid7(),
            fn (): string => $this->createIdentityUserAction->execute(
                'Dev Mutation Actor',
                'dev.mutation.actor.'.uniqid('', true).'@dormsys.local',
            )->requireId()->value,
        );

        return MutationPrincipalContext::runAs($actorId, $callback);
    }
}
