# PR-N5 — Boundary Lock Report

**Date:** 2026-07-15  
**Role:** Investigator → (after Lead authorization) Executor  
**Status:** Execute complete for authorized items; commit/PR pending separate Lead authorization

---

## Lead Decisions (STOP GATEs)

| Gate | Item | Decision |
|------|------|----------|
| STOP GATE 3 | N5-4 Dual Principal | **Option A** — DGAP-10 Evidence Matrix enrichment; status stays CLOSED — NOT-A-GAP; cross-module `UserModel` in Auth/Http = Accepted Intentional |
| N5-2 | Livewire route auth | **Document-only** — no code fix (guest login + `auth:identity` dormitory intentional) |
| STOP GATE 1 | N5-1 Logout | **Option A** — add `auth:api`; guest → 401 |
| STOP GATE 2 | N5-3 Exceptions | Approved table + ambiguous resolutions (physical=422, last-admin=422, reissuance=422, ownership=403); execute in this PR |

---

## N5-4 — Dual Principal (Execute)

- **File:** `docs/governance/open-decisions.md` (DGAP-10)
- **Change:** Added `#### Evidence Matrix` only
- **Unchanged:** Status, Decided-On, Decision-Owner, Decision
- **Corrected finding:** `App\Models\User` does **not** use HasUuids; UUID is on `UserModel` via `BaseModel`→`HasUuid`

---

## N5-2 — Livewire (Document-only)

All production Livewire full-page routes are behind `auth:api` or `auth:identity`, except intentional `EmployeeLogin` under `guest:api` (`routes/web.php` L16–21). Dormitory dashboards: L24–34. App shell group: L37. No route-level auth gap requiring fix.

Framework endpoints `livewire/update|upload` remain without `auth:*` (Livewire platform default) — out of N5-2 “component route” fix scope; mitigated by parent page auth + dormitory `IdentityRoleGuard` in `render()`.

---

## N5-1 — Logout middleware (Execute)

**Before:** `routes/api.php` L16–18 — `POST /logout` middleware chain = `["api"]` only; guest probe → **200** `{"success":true}`.

**After:** `routes/api.php` — `->middleware('auth:api')` on logout.

**Observed after fix (tests):**
- Guest → **401** + `success=false` / `Unauthenticated.`
- Authenticated session → **200** + session cleared (existing test)

---

## N5-3 — Exception HTTP rendering (Execute)

**File:** `bootstrap/app.php` only (api `/*` guards; JSON shape `{success, message}`).

**web/Livewire:** unchanged — handlers return `null` outside `api/*` → Laravel default.

### Mapping summary (Lead-approved)

| Status | Exceptions |
|--------|------------|
| 404 | EmployeeNotFound, DepartmentNotFound, DependentNotFound, UnknownIdentityUser, UserNotFound, RoleNotFound |
| 403 | DependentOwnership, UnauthorizedDormitoryStructureAccess |
| 409 | DuplicateIdentityId, DuplicateDepartmentCode, DuplicateUserEmail, InvalidUserStateTransition, InvalidOccupancyTransition, InvalidResourceStateTransition, InvalidVoucherTransition, DuplicateTriggerCorrelation |
| 422 | IdentityIdImmutable, InactiveDepartmentAssignment, CannotDeactivateLastAdministrator, InvalidCapacity, InvalidDormitoryHierarchy, PhysicalStateSignalRejected, VoucherNotEligibleForIssuance, VoucherReissuanceRejected |

**Pre-fix observe (`APP_DEBUG=false`):** unmapped domain exceptions → HTTP **500** `"Server Error"` (no stack in JSON).

---

## Tests

| Suite | Command | Result |
|-------|---------|--------|
| Baseline (pre-exec, auth) | `php artisan test --filter=ApiAuthSessionEntryTest` | passed |
| N5 auth + exception | `php artisan test --filter="ApiAuthSessionEntryTest\|PrN5BoundaryLockExceptionRenderingTest"` | **32 passed** |
| RequestUiFlow (alone) | `php artisan test tests/Feature/Modules/Request/RequestUiFlowTest.php` | **8 passed** |
| Full suite (Exit Gate re-verify) | `php artisan test` | **1872 passed**, 4 skipped, **0 failed** (`duration_ms` ≈ 624616) |

Note: An earlier post-exec full run once showed 2 transient PostgreSQL race/deadlock errors on `RequestUiFlowTest`; serial re-run of that file was green, and the Exit Gate full re-run above is clean.

**New/updated tests:**
- `tests/Feature/Auth/ApiAuthSessionEntryTest.php` — guest logout 401
- `tests/Feature/Boundary/PrN5BoundaryLockExceptionRenderingTest.php` — one case per mapped exception + web non-mapping smoke

---

## `git diff --stat` (authorization scope check)

```
 bootstrap/app.php                              | 103 ++++++++++++++++++++++++-
 docs/governance/open-decisions.md              |  19 +++++
 routes/api.php                                 |   3 +-
 tests/Feature/Auth/ApiAuthSessionEntryTest.php |   7 ++
 tests/Feature/Boundary/ (new)                  | untracked until add
 docs/release/pr-n5-boundary-lock.md            | this report
```

**Code authorize list:** `routes/api.php`, `bootstrap/app.php` — satisfied.  
**Docs authorize:** DGAP-10 enrichment — satisfied.  
**Tests:** authorized by Lead table — satisfied.  
**No other production code files modified.**

---

## Exit Gate checklist

| Check | Status |
|-------|--------|
| Each STOP GATE has explicit Lead decision | Yes (recorded above) |
| Tests green for N5 scope | Yes (32/32) |
| Full suite | Yes — 1872 passed, 4 skipped, 0 failed (Exit Gate re-verify) |
| Diff scoped to authorization | Yes (see file list; memory log optional) |
| This report complete | Yes |
| Commit / PR | **Blocked** — needs separate Lead authorization |

---

## Recommended next Lead action

1. Authorize `git add` + commit of the files listed in `git diff --stat` + new Boundary test + this report  
2. Optionally re-run full suite with serial/`--processes=1` if CI uses parallel workers against one Postgres `testing` DB
