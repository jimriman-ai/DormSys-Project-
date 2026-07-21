# T2-4 Session Handoff — Stage1ApproverConsoleFilterTest

_Date: 2026-07-21 (1405/04/30)_

## Status

**COMPLETED** — verified **8 passed**; commit `f8cec6b`

## Verify

```
docker compose exec -T laravel.test php artisan test --no-ansi --filter=Stage1ApproverConsoleFilterTest
Tests:    8 passed (34 assertions)
```

## Commit

```
f8cec6b test: fix Stage1ApproverConsoleFilterTest approver identity + singleton reset (T2-4, test-only)
file: tests/Feature/Modules/Request/Stage1ApproverConsoleFilterTest.php
```

## Changes (test-only)

1. Pass `$approver` into `createSubmittedStage1PersonalRequest($approver)` at list/filter/empty/clear sites.
2. `forgetInstance` on `AssignStage1ApproverSnapshotAction` and `CreatePersonalRequestAction` in `bindStage1ConsoleApproverAsSnapshotSource`.

## STEP 3 Feature FAIL|ERROR snapshot (as-is, head -40)

```
   FAIL  Tests\Feature\Auth\ApiAuthSessionEntryTest
   FAIL  Tests\Feature\Modules\Allocation\LotteryAllocationHttpFlowTest
   FAIL  Tests\Feature\Modules\Lottery\LotteryBackgroundJobsTest
   FAIL  Tests\Feature\Modules\Lottery\LotteryHttpFlowCompletionTest
   FAIL  Tests\Feature\Modules\Lottery\LotteryIntegrationBoundaryTest
   FAIL  Tests\Feature\Modules\Lottery\LotteryMutationAuthorizationTest
   FAIL  Tests\Feature\Modules\Lottery\LotteryProgramDrawTest
   FAIL  Tests\Feature\Modules\Lottery\LotteryProgramLockTest
   FAIL  Tests\Feature\Modules\Lottery\LotteryResultReadContractTest
   FAIL  Tests\Feature\Modules\Lottery\LotterySemanticIsolationTest
   FAIL  Tests\Feature\Modules\Request\LotteryRegistrationRequestTest
   FAIL  Tests\Feature\Modules\Request\RequestListDetailNavigationUiFlowTest
   FAIL  Tests\Feature\Modules\Request\RequestReadContractTest
   FAIL  Tests\Feature\Mutation\MutationRuntimeGovernanceTest
   FAIL  Tests\Feature\Mutation\MutationSurfaceStabilizationTest
   FAIL  Tests\Feature\Production\ProductionHttpHardeningTest
   FAILED  Tests\Feature\Auth\ApiAuthSessionEntryTest > it r…  QueryException   
  SQLSTATE[40P01]: Deadlock detected: 7 ERROR:  deadlock detected
   FAILED  Tests\Feature\Auth\ApiAuthSessionEntryTest > it r…  QueryException   
  SQLSTATE[40P01]: Deadlock detected: 7 ERROR:  deadlock detected
   FAILED  Tests\Feature\Modules\Allocati…  InvalidRequestTransitionException   
   FAILED  Tests\Feature\Modules\Allocation\LotteryAllocationHttpFlowTest >…    
   FAILED  Tests\Feature\Modules\Allocation\LotteryAllocationHttpFlowTest >…    
   FAILED  Tests\Feature\Modules\Allocati…  InvalidRequestTransitionException   
   FAILED  Tests\Feature\Modules\Lottery…  InvalidRequestTransitionException   
   … (head -40 cut; remainder Lottery FAILED lines)
```

## Suspension

**SUSPEND** — awaiting Lead decision on next T2 cluster. No auto-fix.
