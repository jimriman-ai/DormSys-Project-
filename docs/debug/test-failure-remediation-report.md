# Test Failure Remediation Report

**Date:** 2026-07-12  
**Scope:** Strict remediation of `php artisan test` failures and risky tests

---

## 1. Initial test state

**User-reported:**

| Metric | Count |
| ------ | ----- |
| Failed | 18 |
| Risky | 11 |
| Skipped | 4 |
| Passed | 1752 |
| Assertions | 4853 |
| Duration | 311.18s |

**Evidence capture (this run, before remediation):** 8 failed / 10 additional draw-path errors after first fixture pass / 11 risky / 4 skipped — all rooted in Spec04 live bed assignability (`BedNotAssignableException`).

Analysis artifact: `docs/debug/test-failure-analysis-report.md`

---

## 2. Failure-by-failure resolution

| Test | Root cause | Fix | Files changed | Status |
| ---- | ---------- | --- | ------------- | ------ |
| CheckInHttpFlowCompletionTest (4 cases) | Invented `bedId` UUIDs; live `DormitoryAssignabilityReadBridge` rejects missing beds | Seed via `createAssignableBedForAllocationTests()` | `tests/Feature/Modules/CheckIn/CheckInHttpFlowCompletionTest.php` | Fixed |
| LotteryHttpFlowCompletionTest E2E draw | Draw allocates `dormitory_id` as `bedId` without Spec04 bed row | `createAssignableBedForAllocationTests(id: $dormitoryId)` | `tests/Feature/Modules/Lottery/LotteryHttpFlowCompletionTest.php` | Fixed |
| ProductionHttpHardeningTest (3 cases) | Same invented bed / lottery dormitory-as-bed gap | Seed real assignable beds | `tests/Feature/Production/ProductionHttpHardeningTest.php` | Fixed |
| LotteryBackgroundJobs / IntegrationBoundary / ProgramDraw / ResultRead / SemanticIsolation / MutationRuntimeGovernance (10 errors) | Same lottery dormitory→bed gap across helpers | Seed assignable bed inside `createDormitorySiteForRequestTests()` + idempotent bed helper | `tests/Feature/Modules/Request/support/dormitory-site.php`, `tests/Feature/Modules/Allocation/support/assignable-bed.php` | Fixed |
| MutationRuntimePrincipalContainmentTest consumer path | Invented `dormitory_id` for `ProposedAllocationConsumer` | Use `createAssignableBedForAllocationTests()` as dormitory/bed id | `tests/Feature/Mutation/MutationRuntimePrincipalContainmentTest.php` | Fixed |

**Classification:** all **B) Test fixture/factory issue**. Domain assignability preserved (no Null adapter rebinding).

---

## 3. Risky tests resolution

| Risky cause | Tests | Fix |
| ----------- | ----- | --- |
| Zero assertions: bare `app(Contract::class)` | Architecture binding checks + LotteryDrivenAllocation binding | Assert `app()->bound(...)` and concrete `::class` |
| Zero assertions: empty exclusion `foreach` | ModuleInventoryParity exclusion tests | Assert `architectureMatrixExcludedActiveModules()->toBe([])` |
| Zero assertions: validate without expect | MissionGroupValidator “accepts valid group” | `expect(...)->not->toThrow(...)` |

Also fixed parallel bootstrap warning: removed ineffective `use Mockery;` in `RequestLifecycleHandoffTest` (kept `Mockery::type` in file namespace).

---

## 4. Final test results

### Sequential

```text
php -d memory_limit=512M artisan test
{"tool":"pest","result":"passed","tests":1785,"passed":1781,"assertions":5074,"duration_ms":304069,"skipped":4}
EXIT=0
```

Zero failures. Zero risky.

### Parallel

```text
php -d memory_limit=512M artisan test --parallel
{"tool":"pest","result":"passed","tests":1810,"passed":1806,"assertions":5168,"duration_ms":108338,"skipped":4}
PARALLEL_EXIT=0
```

### Quality gates

| Gate | Command | Result |
| ---- | ------- | ------ |
| PHPStan | `php vendor/bin/phpstan analyse --no-progress` | **passed, 0 errors** |
| Pint | `php vendor/bin/pint --test` | **Pre-existing fails** in unrelated Request DTOs (`EmployeeRequestListQueryDTO`, `PaginatedRequestSummaryListDTO`, `RequestEmployeeListFilterOptions` — line endings). Allocation unused-import files fixed. **No new Pint failures in remedited files.** |

---

## 5. Files changed

### Fixtures / helpers
- `tests/Feature/Modules/Request/support/dormitory-site.php`
- `tests/Feature/Modules/Allocation/support/assignable-bed.php`

### Feature / mutation tests
- `tests/Feature/Modules/CheckIn/CheckInHttpFlowCompletionTest.php`
- `tests/Feature/Modules/Lottery/LotteryHttpFlowCompletionTest.php`
- `tests/Feature/Production/ProductionHttpHardeningTest.php`
- `tests/Feature/Mutation/MutationRuntimePrincipalContainmentTest.php`
- `tests/Feature/Modules/Allocation/LotteryDrivenAllocationTest.php`
- `tests/Feature/Modules/Allocation/RequestLifecycleHandoffTest.php`
- (+ Pint unused-import cleanup on AllocationIntegrationBoundaryTest / RequestDrivenAllocationTest)

### Architecture / unit (risky)
- `tests/Architecture/ReportingBoundaryTest.php`
- `tests/Architecture/AllocationBoundaryTest.php`
- `tests/Architecture/AuditBoundaryTest.php`
- `tests/Architecture/LotterySupplierBoundaryTest.php`
- `tests/Architecture/NotificationBoundaryTest.php`
- `tests/Architecture/ModuleInventoryParityTest.php`
- `tests/Unit/Modules/Request/Domain/MissionGroupValidatorTest.php`

### Docs
- `docs/debug/test-failure-analysis-report.md`
- `docs/debug/test-failure-remediation-report.md` (this file)

**Production code:** unchanged in this remediation pass (live assignability kept).

---

## 6. Root causes (summary)

1. **Live Spec04 assignability** (`DormitoryAssignabilityReadBridge`) requires persisted vacant beds; HTTP/Lottery fixtures still invented UUIDs (or used dormitory UUID as bed without a bed row).
2. **Risky:** several architecture/binding tests resolved the container without assertions; empty matrix-exclusion loops performed zero assertions.

---

## 7. Governance impact

- No domain rule weakening; no Null default rebinding.
- Fixture alignment matches approved Integration-layer live wiring and existing Allocation helper patterns (`LotteryAllocationHttpFlowTest`).
- No `.specify/governance` authorization reopen; test-only remediation.

---

## 8. Remaining blockers

**None for test failures / risky.**

**Noted (out of remediation scope):** full-repo `pint --test` still reports pre-existing Request DTO line-ending issues unrelated to this fix set.
