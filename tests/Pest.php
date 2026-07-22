<?php

declare(strict_types=1);
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

require_once __DIR__.'/Architecture/architecture.php';
require_once __DIR__.'/Support/mutation-acting.php';
require_once __DIR__.'/Support/mutation-bypass.php';
require_once __DIR__.'/Support/serial-shared-db-lock.php';
require_once __DIR__.'/Feature/Modules/Request/support/mutation-principal.php';
require_once __DIR__.'/Feature/Modules/Request/support/http-mutation.php';
require_once __DIR__.'/Feature/Modules/Request/support/dormitory-site.php';
require_once __DIR__.'/Feature/Modules/Request/support/stage1-snapshot.php';
require_once __DIR__.'/Feature/Modules/Request/support/stage1-console.php';
require_once __DIR__.'/Feature/Modules/CheckIn/support/mutation-principal.php';
require_once __DIR__.'/Feature/Modules/Lottery/support/mutation-principal.php';
require_once __DIR__.'/Feature/Modules/Lottery/support/http-mutation.php';
require_once __DIR__.'/Feature/Modules/Dormitory/support/structure-authorization.php';
require_once __DIR__.'/Feature/Modules/Allocation/support/mutation-principal.php';
require_once __DIR__.'/Feature/Modules/Allocation/support/http-mutation.php';
require_once __DIR__.'/Feature/Modules/Allocation/support/assignable-bed.php';

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

uses()
    ->beforeEach(function (): void {
        bindStage1ApproverIdentityFixtureForTests();
    })
    ->in(
        'Feature/Modules/Request',
        'Feature/Modules/Allocation',
        'Feature/Modules/CheckIn',
        'Feature/Modules/Lottery',
        'Feature/Mutation',
        'Feature/Production',
    );

pest()->extend(TestCase::class)->in('Architecture');

pest()->extend(TestCase::class)->in('Unit');

pest()->group('architecture')->in('Architecture');

/*
|--------------------------------------------------------------------------
| Serial shared-DB suites (HD-02 Option B / G-REQ)
|--------------------------------------------------------------------------
|
| Must not run in parallel against the same `testing` database (deadlock).
| Groups: g-req-guards, hd02-option-b, serial-shared-db
| Canonical verify: --testsuite=GReqGuards then --testsuite=Hd02OptionB
|
*/

$serialSharedDbPaths = [
    'Architecture/RequestTestApiGuardTest.php',
    'Architecture/Stage1HelperContractTest.php',
    'Architecture/RequestDomainBoundaryTest.php',
    'Architecture/PresentationEventDispatchGuardTest.php',
    'Architecture/PresentationCommandDispatchGuardTest.php',
    'Architecture/PresentationRawQueryGuardTest.php',
    'Architecture/PresentationServiceLocatorGuardTest.php',
    'Feature/Modules/Request/RequestTransitionGuardTest.php',
    'Feature/Modules/Lottery/LotteryProgramLifecycleTest.php',
    'Feature/Modules/Lottery/LotteryFoundationTest.php',
    'Unit/Modules/Lottery/Application/LotteryProgramActionsTest.php',
    'Unit/Modules/Lottery/Domain/LotteryProgramEntityTest.php',
    'Unit/Modules/Lottery/Domain/LotteryProgramStateTest.php',
    'Unit/Modules/Lottery/Domain/LotteryProgramTransitionMatrixTest.php',
    'Unit/Modules/Lottery/Domain/LotteryProgramLifecycleTest.php',
    'Unit/Modules/Lottery/Domain/LotteryValueObjectsTest.php',
    'Unit/Modules/Lottery/Domain/LotteryExceptionsTest.php',
    'Unit/Modules/Lottery/Domain/LotteryScoringEngineTest.php',
    'Unit/Modules/Lottery/Domain/LotteryDrawSelectorTest.php',
    'Unit/Modules/Lottery/Domain/LockedLotterySemanticContractTest.php',
];

$gReqGuardPaths = [
    'Architecture/RequestTestApiGuardTest.php',
    'Architecture/Stage1HelperContractTest.php',
    'Architecture/RequestDomainBoundaryTest.php',
    'Architecture/PresentationEventDispatchGuardTest.php',
    'Architecture/PresentationCommandDispatchGuardTest.php',
    'Architecture/PresentationRawQueryGuardTest.php',
    'Architecture/PresentationServiceLocatorGuardTest.php',
    'Feature/Modules/Request/RequestTransitionGuardTest.php',
];

$hd02OptionBPaths = [
    'Feature/Modules/Lottery/LotteryProgramLifecycleTest.php',
    'Feature/Modules/Lottery/LotteryFoundationTest.php',
    'Unit/Modules/Lottery/Application/LotteryProgramActionsTest.php',
    'Unit/Modules/Lottery/Domain/LotteryProgramEntityTest.php',
    'Unit/Modules/Lottery/Domain/LotteryProgramStateTest.php',
    'Unit/Modules/Lottery/Domain/LotteryProgramTransitionMatrixTest.php',
    'Unit/Modules/Lottery/Domain/LotteryProgramLifecycleTest.php',
    'Unit/Modules/Lottery/Domain/LotteryValueObjectsTest.php',
    'Unit/Modules/Lottery/Domain/LotteryExceptionsTest.php',
    'Unit/Modules/Lottery/Domain/LotteryScoringEngineTest.php',
    'Unit/Modules/Lottery/Domain/LotteryDrawSelectorTest.php',
    'Unit/Modules/Lottery/Domain/LockedLotterySemanticContractTest.php',
];

pest()->group('serial-shared-db')->in(...$serialSharedDbPaths);
pest()->group('g-req-guards')->in(...$gReqGuardPaths);
pest()->group('hd02-option-b')->in(...$hd02OptionBPaths);

uses()
    ->beforeEach(function (): void {
        acquireTestingSerialSharedDbLock();
    })
    ->afterEach(function (): void {
        releaseTestingSerialSharedDbLock();
    })
    ->in(...$serialSharedDbPaths);