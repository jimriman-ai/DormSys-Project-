<?php

declare(strict_types=1);

use App\Application\Mutation\Support\MutationPrincipalContextHolder;
use App\Modules\Employee\Application\Contracts\EmployeeRepositoryContract;
use App\Modules\Identity\Application\Contracts\IdentityUserReadContract;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use App\Shared\ValueObjects\SystemActorId;
use Tests\Support\MockeryTest;

function seedMutationAuthorizationBypassPrincipal(): string
{
    $principalId = UuidGenerator::uuid7();
    app(MutationPrincipalContextHolder::class)->set($principalId);

    return $principalId;
}

function bypassLotteryMutationAuthorization(): void
{
    app(MutationPrincipalContextHolder::class)->set(SystemActorId::VALUE);
}

function bypassAllocationMutationAuthorization(): void
{
    $principalId = seedMutationAuthorizationBypassPrincipal();

    $identityRead = MockeryTest::mock(IdentityUserReadContract::class);
    MockeryTest::expect($identityRead, 'isUserActive')->with($principalId)->andReturn(true);
    MockeryTest::expect($identityRead, 'isUserActive')->withAnyArgs()->andReturn(true);
    app()->instance(IdentityUserReadContract::class, $identityRead);
}

function configureLotteryEnrollMutationAuthorization(string $principalId, string $employeeId): void
{
    app(MutationPrincipalContextHolder::class)->set($principalId);

    $employees = MockeryTest::mock(EmployeeRepositoryContract::class);
    MockeryTest::expect($employees, 'findEmployeeIdByIdentityUserId')
        ->with($principalId)
        ->andReturn($employeeId);
    app()->instance(EmployeeRepositoryContract::class, $employees);

    $identityRead = MockeryTest::mock(IdentityUserReadContract::class);
    MockeryTest::expect($identityRead, 'isUserActive')->with($principalId)->andReturn(true);
    app()->instance(IdentityUserReadContract::class, $identityRead);
}

function resetMutationAuthorizationTestState(): void
{
    app()->forgetInstance(EmployeeRepositoryContract::class);
    app()->forgetInstance(IdentityUserReadContract::class);

    if (app()->bound(MutationPrincipalContextHolder::class)) {
        app(MutationPrincipalContextHolder::class)->clear();
    }

    request()->attributes->remove('audit_principal_user_id');
}
