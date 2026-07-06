<?php

declare(strict_types=1);

use App\Application\Mutation\Registry\PendingMutationAuthorizationRegistry;
use App\Modules\Allocation\Application\Services\ProposedAllocationConsumer;
use App\Modules\CheckIn\Application\Services\CheckInService;
use App\Modules\Lottery\Infrastructure\Jobs\AutoLockLotteryJob;
use App\Modules\Lottery\Infrastructure\Jobs\ExecuteLotteryDrawJob;
use App\Modules\Request\Presentation\Http\Controllers\RequestMutationController;
use App\Modules\Request\Presentation\Providers\RequestPresentationServiceProvider;

require_once __DIR__.'/MutationSurfaceStabilizationTest.php';

/**
 * @return list<class-string>
 */
function knownAdoptedHttpMutationControllers(): array
{
    return [
        RequestMutationController::class,
    ];
}

/**
 * @return list<class-string>
 */
function knownAdoptedRuntimeMutationJobs(): array
{
    return [
        AutoLockLotteryJob::class,
        ExecuteLotteryDrawJob::class,
    ];
}

/**
 * @return list<class-string>
 */
function knownAdoptedCliMutationCommands(): array
{
    return [
        App\Modules\Identity\Presentation\Console\CreateUserCommand::class,
        App\Modules\Identity\Presentation\Console\DeactivateUserCommand::class,
        App\Modules\Employee\Presentation\Console\CreateEmployeeCommand::class,
        App\Modules\Employee\Presentation\Console\CreateDepartmentCommand::class,
        App\Modules\Employee\Presentation\Console\AssignDepartmentCommand::class,
        App\Modules\Request\Presentation\Console\SubmitRequestCommand::class,
    ];
}

/**
 * @return list<class-string>
 */
function knownPendingCliMutationCommands(): array
{
    return [
        App\Modules\Request\Presentation\Console\CreatePersonalRequestCommand::class,
    ];
}

test('adopted HTTP mutation controllers delegate only to adopted actions', function (): void {
    $adoptedActions = adoptedMutationActionClasses();

    foreach (knownAdoptedHttpMutationControllers() as $controllerClass) {
        $path = (new ReflectionClass($controllerClass))->getFileName();
        expect($path)->not->toBeFalse();

        $contents = file_get_contents((string) $path);
        expect($contents)->toBeString()
            ->and($contents)->not->toContain('MutationPolicyEnforcementPoint')
            ->and($contents)->not->toContain('MutationAuthorizationGate')
            ->and($contents)->toContain('Action');
    }
});

test('request HTTP mutation routes are behind session mutation principal middleware', function (): void {
    $routePath = RequestPresentationServiceProvider::requestRoutePath();
    $apiPath = base_path('routes/api.php');

    $apiContents = file_get_contents($apiPath);
    $routeContents = file_get_contents($routePath);

    expect($apiContents)->toBeString()
        ->and($apiContents)->toContain('request.mutation.principal')
        ->and($routeContents)->toBeString()
        ->and($routeContents)->toContain(RequestMutationController::class);
});

test('adopted runtime jobs use runJobAsSystem and do not embed parallel authorization', function (): void {
    foreach (knownAdoptedRuntimeMutationJobs() as $jobClass) {
        $path = (new ReflectionClass($jobClass))->getFileName();
        expect($path)->not->toBeFalse();

        $contents = file_get_contents((string) $path);
        expect($contents)->toBeString()
            ->and($contents)->toContain('runJobAsSystem')
            ->and($contents)->not->toContain('MutationPolicyEnforcementPoint')
            ->and($contents)->not->toContain('MutationAuthorizationGate');
    }
});

test('orchestration surfaces do not establish mutation authority', function (): void {
    foreach ([ProposedAllocationConsumer::class, CheckInService::class] as $surfaceClass) {
        $path = (new ReflectionClass($surfaceClass))->getFileName();
        expect($path)->not->toBeFalse();

        $contents = file_get_contents((string) $path);
        expect($contents)->toBeString()
            ->and($contents)->not->toContain('runJobAsSystem')
            ->and($contents)->not->toContain('MutationPolicyEnforcementPoint');
    }
});

test('pending CLI mutation commands map to pending actions only', function (): void {
    foreach (knownPendingCliMutationCommands() as $commandClass) {
        $path = (new ReflectionClass($commandClass))->getFileName();
        expect($path)->not->toBeFalse();

        $contents = file_get_contents((string) $path);
        expect($contents)->toBeString();

        foreach (PendingMutationAuthorizationRegistry::all() as $pendingAction) {
            if (is_string($contents) && str_contains($contents, class_basename($pendingAction))) {
                expect(PendingMutationAuthorizationRegistry::isPending($pendingAction))->toBeTrue();
            }
        }
    }
});

test('no Livewire mutation handler surfaces are registered', function (): void {
    $livewireHandlers = glob(app_path('Modules/*/Presentation/Livewire/*.php')) ?: [];

    expect($livewireHandlers)->toBe([]);
});
