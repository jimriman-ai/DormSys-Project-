# Wave 1 Baseline — Known Failures (Option A)

**Recorded:** 2026-07-21  
**Command:** `docker compose exec -T laravel.test php artisan test --no-ansi`  
**Result:** **43 failed**, **1912 passed** (5355 assertions), ~1349.7s, exit 2  

**Disposition (Lead):** Treat as **known-fail** outside Wave 1 Auth remediation gate. Do **not** use this red suite as a green baseline. Wave 1 pass/fail focuses on Auth Foundation + allowlist + T4 inventory.

## Exclusion guidance (for Wave 1 local gates)

Prefer running a **scoped** suite for Wave 1 verification, e.g.:

```bash
docker compose exec -T laravel.test php artisan test --no-ansi tests/Feature/Auth tests/Unit --filter=Auth
```

Full suite remains the program truth; failures below are **out of Wave 1 fix scope**.

## Failure clusters (observed)

| Cluster | Examples | Wave 1 action |
|---------|----------|---------------|
| Lottery Feature | `LotteryBackgroundJobsTest`, `LotteryHttpFlowCompletionTest`, `LotteryMutationAuthorizationTest`, … | **FROZEN (HD-02)** — do not fix |
| Request transition | `InvalidRequestTransitionException` in Request HTTP / Mutation / Stage1 / Production hardening | WF/request cutover debt — **DEBT-DISCOVERY**; not T1–T4 |
| Architecture | `ForbiddenImportsScanTest`, `ModuleBoundaryTest`, `ModuleInventoryParityTest`, `MutationAuthorizationBoundaryTest` (Workflow actions) | Module/WF inventory drift — out of scope |
| Unit Request | `SubmitDateValidationTest` (`ArgumentCountError`) | Out of scope |

## Auth Foundation slice (expected Wave 1 green)

Re-run after T1 allowlist docs:

- `tests/Feature/Auth/LoginUserActionTest.php`
- `tests/Feature/Auth/LogoutUserActionTest.php`
- `tests/Feature/Auth/GetCurrentAuthUserActionTest.php`

(T2 will later replace bare `auth()` in these files — currently PARKED.)
