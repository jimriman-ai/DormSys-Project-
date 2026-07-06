<?php

declare(strict_types=1);

use App\Application\Mutation\Support\MutationPrincipalContextHolder;
use App\Modules\Employee\Application\Contracts\EmployeeRepositoryContract;
use App\Modules\Identity\Application\Contracts\IdentityUserReadContract;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use App\Shared\ValueObjects\SystemActorId;

function seedMutationAuthorizationBypassPrincipal(): string
{
    $principalId = UuidGenerator::uuid7();
    putenv('MUTATION_ACTING_PRINCIPAL='.$principalId);
    $_ENV['MUTATION_ACTING_PRINCIPAL'] = $principalId;
    $_SERVER['MUTATION_ACTING_PRINCIPAL'] = $principalId;

    return $principalId;
}

function bypassLotteryMutationAuthorization(): void
{
    putenv('MUTATION_ACTING_PRINCIPAL='.SystemActorId::VALUE);
    $_ENV['MUTATION_ACTING_PRINCIPAL'] = SystemActorId::VALUE;
    $_SERVER['MUTATION_ACTING_PRINCIPAL'] = SystemActorId::VALUE;
}

function bypassAllocationMutationAuthorization(): void
{
    $principalId = seedMutationAuthorizationBypassPrincipal();

    $identityRead = \Mockery::mock(IdentityUserReadContract::class);
    $identityRead->shouldReceive('isUserActive')->with($principalId)->andReturn(true);
    $identityRead->shouldReceive('isUserActive')->withAnyArgs()->andReturn(true);
    app()->instance(IdentityUserReadContract::class, $identityRead);
}

function configureLotteryEnrollMutationAuthorization(string $principalId, string $employeeId): void
{
    putenv('MUTATION_ACTING_PRINCIPAL='.$principalId);
    $_ENV['MUTATION_ACTING_PRINCIPAL'] = $principalId;
    $_SERVER['MUTATION_ACTING_PRINCIPAL'] = $principalId;

    $employees = \Mockery::mock(EmployeeRepositoryContract::class);
    $employees->shouldReceive('findEmployeeIdByIdentityUserId')
        ->with($principalId)
        ->andReturn($employeeId);
    app()->instance(EmployeeRepositoryContract::class, $employees);

    $identityRead = \Mockery::mock(IdentityUserReadContract::class);
    $identityRead->shouldReceive('isUserActive')->with($principalId)->andReturn(true);
    app()->instance(IdentityUserReadContract::class, $identityRead);
}

function resetMutationAuthorizationTestState(): void
{
    putenv('MUTATION_ACTING_PRINCIPAL');
    unset($_ENV['MUTATION_ACTING_PRINCIPAL'], $_SERVER['MUTATION_ACTING_PRINCIPAL']);

    app()->forgetInstance(EmployeeRepositoryContract::class);
    app()->forgetInstance(IdentityUserReadContract::class);

    if (app()->bound(MutationPrincipalContextHolder::class)) {
        app(MutationPrincipalContextHolder::class)->clear();
    }

    request()->attributes->remove('audit_principal_user_id');
}
