<?php

declare(strict_types=1);

use App\Application\Mutation\Exceptions\UnauthorizedMutationException;
use App\Application\Mutation\Registry\PendingMutationAuthorizationRegistry;
use App\Application\Mutation\Services\MutationPolicyEnforcementPoint;
use App\Modules\Allocation\Application\Services\CreateAllocationAction;
use App\Modules\Allocation\Application\Services\ProposedAllocationConsumer;
use App\Modules\CheckIn\Application\Services\CheckInService;
use App\Modules\Identity\Application\Services\CreateUserAction;
use App\Modules\Lottery\Infrastructure\Jobs\AutoLockLotteryJob;
use App\Modules\Lottery\Infrastructure\Jobs\ExecuteLotteryDrawJob;
use App\Modules\Request\Application\Services\SubmitRequestAction;

/**
 * @return list<class-string>
 */
function adoptedMutationActionClasses(): array
{
    return [
        SubmitRequestAction::class,
        App\Modules\Request\Application\Services\CancelRequestAction::class,
        App\Modules\Request\Application\Services\ApproveRequestStageAction::class,
        App\Modules\Request\Application\Services\RejectRequestAction::class,
        App\Modules\CheckIn\Application\Services\CheckInAction::class,
        App\Modules\CheckIn\Application\Services\CheckOutAction::class,
        CreateUserAction::class,
        App\Modules\Identity\Application\Services\DeactivateUserAction::class,
        App\Modules\Identity\Application\Services\AssignRoleToUserAction::class,
        App\Modules\Identity\Application\Services\RevokeRoleFromUserAction::class,
        App\Modules\Employee\Application\Services\CreateEmployeeAction::class,
        App\Modules\Employee\Application\Services\CreateDepartmentAction::class,
        App\Modules\Employee\Application\Services\DeactivateDepartmentAction::class,
        App\Modules\Employee\Application\Services\AssignDepartmentToEmployeeAction::class,
        App\Modules\Lottery\Application\Services\CreateLotteryProgramAction::class,
        App\Modules\Lottery\Application\Services\OpenRegistrationAction::class,
        App\Modules\Lottery\Application\Services\CloseRegistrationAction::class,
        App\Modules\Lottery\Application\Services\CancelLotteryProgramAction::class,
        App\Modules\Lottery\Application\Services\LockLotteryProgramAction::class,
        App\Modules\Lottery\Application\Services\ExecuteDrawAction::class,
        App\Modules\Lottery\Application\Services\EnrollRegistrationAction::class,
        CreateAllocationAction::class,
        App\Modules\Allocation\Application\Services\CreateAllocationFromRequestAction::class,
        App\Modules\Allocation\Application\Services\ReleaseAllocationAction::class,
    ];
}

/**
 * @return list<class-string>
 */
function mutationAuthorizationGateClasses(): array
{
    return [
        App\Modules\Request\Application\Services\RequestMutationAuthorizationGate::class,
        App\Modules\CheckIn\Application\Services\CheckInMutationAuthorizationGate::class,
        App\Modules\Identity\Application\Services\IdentityMutationAuthorizationGate::class,
        App\Modules\Employee\Application\Services\EmployeeMutationAuthorizationGate::class,
        App\Modules\Lottery\Application\Services\LotteryMutationAuthorizationGate::class,
        App\Modules\Allocation\Application\Services\AllocationMutationAuthorizationGate::class,
    ];
}

test('adopted mutation actions are not pending and invoke MPEP', function (): void {
    foreach (adoptedMutationActionClasses() as $actionClass) {
        expect(PendingMutationAuthorizationRegistry::isPending($actionClass))->toBeFalse();

        $path = (new ReflectionClass($actionClass))->getFileName();
        expect($path)->not->toBeFalse();

        $contents = file_get_contents((string) $path);
        expect($contents)->toBeString()
            ->and($contents)->toContain(MutationPolicyEnforcementPoint::class)
            ->and($contents)->toContain('->enforce(');
    }
});

test('domain mutation gates only deny with UnauthorizedMutationException', function (): void {
    foreach (mutationAuthorizationGateClasses() as $gateClass) {
        $path = (new ReflectionClass($gateClass))->getFileName();
        expect($path)->not->toBeFalse();

        $contents = file_get_contents((string) $path);
        expect($contents)->toBeString()
            ->and($contents)->toContain(UnauthorizedMutationException::class)
            ->and($contents)->not->toMatch('/throw new (?!UnauthorizedMutationException)[A-Za-z\\\\]+Exception/');
    }
});

test('runtime mutation jobs establish system principal only through runJobAsSystem', function (): void {
    foreach ([AutoLockLotteryJob::class, ExecuteLotteryDrawJob::class] as $jobClass) {
        $path = (new ReflectionClass($jobClass))->getFileName();
        expect($path)->not->toBeFalse();

        $contents = file_get_contents((string) $path);
        expect($contents)->toBeString()
            ->and($contents)->toContain('runJobAsSystem')
            ->and($contents)->not->toContain('MutationPrincipalContextHolder')
            ->and($contents)->not->toContain('catch (UnauthorizedMutationException');
    }
});

test('consumer and pass-through services do not perform independent mutation authorization', function (): void {
    foreach ([ProposedAllocationConsumer::class, CheckInService::class] as $serviceClass) {
        $path = (new ReflectionClass($serviceClass))->getFileName();
        expect($path)->not->toBeFalse();

        $contents = file_get_contents((string) $path);
        expect($contents)->toBeString()
            ->and($contents)->not->toContain(MutationPolicyEnforcementPoint::class)
            ->and($contents)->not->toContain('MutationAuthorizationGate')
            ->and($contents)->not->toContain('runJobAsSystem')
            ->and($contents)->not->toContain('runAsSystem');
    }
});

test('application code does not catch UnauthorizedMutationException', function (): void {
    $violations = [];

    foreach (glob(app_path('**/*.php')) ?: [] as $path) {
        $contents = file_get_contents($path);

        if ($contents === false || ! str_contains($contents, 'catch (UnauthorizedMutationException')) {
            continue;
        }

        $violations[] = $path;
    }

    expect($violations)->toBe([]);
});

test('production principal holder mutation occurs only through MutationPrincipalContext', function (): void {
    $violations = [];

    foreach (glob(app_path('**/*.php')) ?: [] as $path) {
        if (str_contains($path, 'MutationPrincipalContextHolder.php') || str_contains($path, 'MutationPrincipalContext.php')) {
            continue;
        }

        $contents = file_get_contents($path);

        if ($contents === false) {
            continue;
        }

        if (preg_match('/MutationPrincipalContextHolder[^;]+->set\(/', $contents) === 1) {
            $violations[] = $path;
        }
    }

    expect($violations)->toBe([]);
});
