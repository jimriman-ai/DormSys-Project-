<?php

declare(strict_types=1);

use App\Application\Mutation\Registry\MutationCapabilityCatalog;
use App\Application\Mutation\Registry\PendingMutationAuthorizationRegistry;
use App\Application\Mutation\Services\MutationPolicyEnforcementPoint;
use App\Modules\Allocation\Application\Services\CreateAllocationAction;
use App\Modules\Allocation\Application\Services\CreateAllocationFromRequestAction;
use App\Modules\Allocation\Application\Services\ReleaseAllocationAction;
use App\Modules\Lottery\Application\Services\CancelLotteryProgramAction;
use App\Modules\Lottery\Application\Services\CloseRegistrationAction;
use App\Modules\Lottery\Application\Services\CreateLotteryProgramAction;
use App\Modules\Lottery\Application\Services\EnrollRegistrationAction;
use App\Modules\Lottery\Application\Services\ExecuteDrawAction;
use App\Modules\Lottery\Application\Services\LockLotteryProgramAction;
use App\Modules\Lottery\Application\Services\OpenRegistrationAction;

/**
 * @return list<class-string>
 */
function lockedPrF1MutationActions(): array
{
    return [
        CreateLotteryProgramAction::class,
        OpenRegistrationAction::class,
        CloseRegistrationAction::class,
        CancelLotteryProgramAction::class,
        LockLotteryProgramAction::class,
        ExecuteDrawAction::class,
        EnrollRegistrationAction::class,
        CreateAllocationAction::class,
        CreateAllocationFromRequestAction::class,
        ReleaseAllocationAction::class,
    ];
}

/**
 * @return array<string, string>
 */
function lockedPrF1CapabilityKeys(): array
{
    return [
        CreateLotteryProgramAction::class => MutationCapabilityCatalog::LOTTERY_PROGRAM_CREATE,
        OpenRegistrationAction::class => MutationCapabilityCatalog::LOTTERY_PROGRAM_OPEN_REGISTRATION,
        CloseRegistrationAction::class => MutationCapabilityCatalog::LOTTERY_PROGRAM_CLOSE_REGISTRATION,
        CancelLotteryProgramAction::class => MutationCapabilityCatalog::LOTTERY_PROGRAM_CANCEL,
        LockLotteryProgramAction::class => MutationCapabilityCatalog::LOTTERY_PROGRAM_LOCK,
        ExecuteDrawAction::class => MutationCapabilityCatalog::LOTTERY_PROGRAM_DRAW,
        EnrollRegistrationAction::class => MutationCapabilityCatalog::LOTTERY_ENROLL_OWN,
        CreateAllocationAction::class => MutationCapabilityCatalog::ALLOCATION_CREATE,
        CreateAllocationFromRequestAction::class => MutationCapabilityCatalog::ALLOCATION_CREATE_FROM_REQUEST,
        ReleaseAllocationAction::class => MutationCapabilityCatalog::ALLOCATION_RELEASE,
    ];
}

test('locked PR-F1 actions are adopted and not pending', function (): void {
    foreach (lockedPrF1MutationActions() as $actionClass) {
        expect(PendingMutationAuthorizationRegistry::isPending($actionClass))
            ->toBeFalse("{$actionClass} must not remain in the pending registry after PR-F1 adoption.");
    }
});

test('locked PR-F1 actions reference MPEP enforcement', function (): void {
    foreach (lockedPrF1MutationActions() as $actionClass) {
        $path = (new ReflectionClass($actionClass))->getFileName();

        expect($path)->not->toBeFalse();

        $contents = file_get_contents((string) $path);

        expect($contents)->toBeString()
            ->and($contents)->toContain(MutationPolicyEnforcementPoint::class);
    }
});

test('locked PR-F1 actions invoke MPEP before persistence adapters in execute()', function (): void {
    $violations = [];

    foreach (lockedPrF1MutationActions() as $actionClass) {
        $path = (new ReflectionClass($actionClass))->getFileName();

        if ($path === false) {
            $violations[] = $actionClass;

            continue;
        }

        $contents = file_get_contents($path);

        if ($contents === false) {
            $violations[] = $actionClass;

            continue;
        }

        if (! preg_match('/function execute\([^)]*\)[^{]*\{([^}]*(?:\{[^}]*\}[^}]*)*)/s', $contents, $matches)) {
            $violations[] = $actionClass;

            continue;
        }

        $executeBody = $matches[1];
        $enforcePos = strpos($executeBody, '->enforce(');
        $persistMarkers = ['->save(', 'DB::transaction', '->markAllocated('];
        $firstPersistPos = false;

        foreach ($persistMarkers as $marker) {
            $pos = strpos($executeBody, $marker);

            if ($pos !== false && ($firstPersistPos === false || $pos < $firstPersistPos)) {
                $firstPersistPos = $pos;
            }
        }

        if ($enforcePos === false || ($firstPersistPos !== false && $enforcePos > $firstPersistPos)) {
            $violations[] = $actionClass;
        }
    }

    expect($violations)->toBe([]);
});

test('locked PR-F1 capability keys are registered in the catalog', function (): void {
    $registered = MutationCapabilityCatalog::registeredKeys();

    foreach (lockedPrF1CapabilityKeys() as $actionClass => $capabilityKey) {
        expect($registered)->toContain($capabilityKey);
    }
});

test('locked PR-F1 capability mapping is one-to-one for adopted actions', function (): void {
    expect(lockedPrF1CapabilityKeys())->toHaveCount(count(lockedPrF1MutationActions()))
        ->and(array_values(lockedPrF1CapabilityKeys()))->toEqual(array_unique(array_values(lockedPrF1CapabilityKeys())));
});
