# Test Failure Analysis Report

**Date:** 2026-07-12  
**Command:** `php -d memory_limit=512M artisan test --compact`  
**Captured result:** 8 failed, 11 risky, 4 skipped, 1763 passed (4853 assertions)  
**User-reported starting point:** 18 failed, 11 risky, 4 skipped, 1752 passed (likely pre-capture / partial prior state)

Evidence: `docs/debug/pest-summary-current.json`

---

## 1. Test failure inventory

| # | Test | File | Error | App root | Module |
| - | ---- | ---- | ----- | -------- | ------ |
| 1 | http check-in flow → checks in and out on an active allocation via http | `tests/Feature/Modules/CheckIn/CheckInHttpFlowCompletionTest.php` | Expected 201, got 422; `BedNotAssignableException` | `CreateAllocationAction.php:51` | Allocation → CheckIn |
| 2 | http check-in domain failures → returns conflict on duplicate check-in | same | same | same | same |
| 3 | http check-in domain failures → returns not found when checking out without check-in | same | same | same | same |
| 4 | http check-in domain failures → returns forbidden when user lacks operator role | same | same | same | same |
| 5 | http end-to-end lottery flow → create/open/enroll/close/lock/draw… | `tests/Feature/Modules/Lottery/LotteryHttpFlowCompletionTest.php` | Expected 200, got 422; `BedNotAssignableException` | `CreateAllocationAction` via `ProposedAllocationConsumer.php:38` (`bedId` = winner `dormitory_id`) | Lottery → Allocation |
| 6 | exception mapping → maps allocation overlap to conflict on lottery draw… | `tests/Feature/Production/ProductionHttpHardeningTest.php` | Expected 201, got 422; `BedNotAssignableException` | `CreateAllocationAction.php:51` | Production / Allocation |
| 7 | exception mapping → maps allocation overlap to conflict on allocation root create path | same | same | same | same |
| 8 | response consistency → always returns persisted check-in record id… | same | same | same | same |

**Shared exception:** `App\Modules\Allocation\Domain\Exceptions\BedNotAssignableException: Bed is not assignable.`

**Related path:** live binding `DormitoryReadPort` → `DormitoryAssignabilityReadBridge` → `AllocationAssignabilityContract::isBedAssignable()` requires a persisted Spec04 bed that is vacant/available. Tests invent `UuidGenerator::uuid7()` bed IDs (or lottery uses `dormitory_id` as `bedId` without a matching bed row).

**Fixture already available:** `createAssignableBedForAllocationTests()` in `tests/Feature/Modules/Allocation/support/assignable-bed.php` (loaded via `tests/Pest.php`). Sibling tests (`AllocationHttpFlowCompletionTest`, `LotteryAllocationHttpFlowTest`) already use it.

---

## 2. Classification (Phase 2)

| Failure group | Category | Evidence |
| ------------- | -------- | -------- |
| CheckIn HTTP (1–4) | **B) Test fixture/factory issue** | Domain assignability is intentional Spec04 live wiring; fixtures still invent bed UUIDs. Not an app regression. |
| Lottery HTTP E2E (5) | **B) Test fixture/factory issue** | `ProposedAllocationConsumer` uses `dormitory_id` as `bedId`; need `createAssignableBedForAllocationTests(id: $dormitoryId)` (same pattern as `LotteryAllocationHttpFlowTest`). |
| Production hardening (6–8) | **B) Test fixture/factory issue** | Same invented bed IDs; overlap/check-in scenarios require real assignable beds so the intended exception path (overlap / check-in) can run. |
| Risky (11) | Pending name capture after failure fix | Count only in Pest JSON; investigate after green failures. |

**Not D:** Expectations (201/200 for create/draw) remain valid once beds exist.  
**Not A:** Live assignability correctly rejects missing beds.  
**Not E:** Bridges already under `app/Integrations/` per policy.

---

## 3. Root cause hypothesis

Spec04 Allocation→Dormitory live assignability (`DormitoryAssignabilityReadBridge`) replaced UUID-format-only `NullDormitoryReadAdapter` as the default. Feature HTTP tests that still invent bed UUIDs now fail at `CreateAllocationAction` before reaching CheckIn/Lottery/overlap assertions.

---

## 4. Proposed remediation

1. Replace invented `bedId` values with `createAssignableBedForAllocationTests()` in CheckIn + Production HTTP tests.
2. For lottery draw paths, seed `createAssignableBedForAllocationTests(id: $dormitoryId)` after creating the dormitory site.
3. Do **not** rebind Null adapter as default; preserve live domain validation.
4. After failures clear, identify and fix 11 risky tests at the underlying cause.

**Risk:** Low — fixture-only alignment already proven in Allocation module tests.

---

## 5. Risk assessment

| Risk | Mitigation |
| ---- | ---------- |
| Lottery bed id == dormitory id semantic quirk | Follow existing `LotteryAllocationHttpFlowTest` / helper PHPDoc pattern |
| Physical state signal after create | Helper creates Vacant/Available beds; live path already covered by `AllocationAssignabilityLivePathTest` |
| Weakening suite | No assertion removal; strengthens coverage of live assignability |
