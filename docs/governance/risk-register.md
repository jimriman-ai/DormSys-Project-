# Risk Register — DormSys

**Authority:** DG-04 Option C (Lead, 1405/04/24)  
**Canonical path:** `docs/governance/risk-register.md`  
**First Phase G seed:** 2026-07-15 (1405/04/24)

| ID | Risk | Impact | Likelihood | Owner (per-risk) | Mitigation | Review Cadence | Status |
|----|------|--------|------------|------------------|------------|----------------|--------|
| SEC-G-01 | Spatie `role:` middleware accepts same role name on `web` guard for identity routes | High (authz bypass) | Medium (pre-fix) | Tech Lead | `EnsureIdentityRole` + `IdentityRoleGuard` require `guard_name = identity` | 6 months / major phase | Mitigated |
| SEC-G-02 | Livewire subsequent requests skip Spatie route middleware role check | High (stale privilege) | Medium | Tech Lead | `assertIdentityRole()` first in both dashboard `render()` methods | 6 months / major phase | Mitigated |
| SEC-G-03 | Public Livewire props hydratable for query-derived collections | Medium (client tampering) | Low | Tech Lead | Collections local to `render()` + `->with()` | 6 months | Mitigated |
| SEC-G-04 | Internal UUIDs exposed in `data-*-id` Blade attributes | Low (recon only if already authorized) | Low | Tech Lead | Accepted risk (H-04b); IDOR closed by assignment scoping when schema present | 6 months / major phase start | Accepted |
| BL-B1-01 | **B1 deferred — schema gap if dependent feature ships** (assignment tables removed from Phase G tip; dashboards no longer query them) | High (assignment/occupancy UI cannot ship) | High if Stage-3 / assignment UI started without tables | Tech Lead | Schema restored RM-BL-B1 (2026-07-16): `dormitory_manager_assignments` + `dormitory_unit_manager_assignments` (`2026_07_16_000001/000002`); dashboards wired; B1 tests restored. | On Phase H start or consumer needing assignments | **RESOLVED (pending Lead commit)** |
| G-E-R-CACHE | Spatie permission Redis cache + RefreshDatabase pollution under dual-guard | Medium (flaky PermissionDoesNotExist) | Medium in CI | Tech Lead | Test env `PERMISSION_CACHE_STORE=array`; seeder mirrors permissions on `web`+`identity` | After CI flake | Mitigated (test) |
| F-W07-02 | EmployeeLogin Establish-fail path: only default `LogoutUserAction` / `Auth::logout()` — not full multi-guard invalidate parity with `AuthSessionController::destroy` | Medium if Establish ever bound before fail | Low (Establish returns false before `api`/`identity` login) | Lead | **Risk accepted** (Lead); **verified** by W08-C-01/C-02 — Establish fail leaves `api`/`identity` guests. See `docs/features/employee-auth-ui/w07-security-review-report.md` | On F2 reopen / auth path change | Accepted + verified (W-08) |
| TD-FE-001 | PHPUnit Vite manifest stub persists into browser runtime asset path (`public/build/manifest.json`) | Medium (local UI without CSS; `assets/app.css` 404) | Medium for local/dev after test runs | Tech Lead | **Accepted technical debt** (Lead). Workaround: `npm run build` after tests. Do **not** fix test bootstrap now. Future remediation: tests must not write under `public/build`; isolate test manifest handling. | Multiple developers onboarded / incident recurrence / pre-release hardening | Accepted (workflow debt) |

## BL-B1-01 — Assignment / B1 behavior tests (restored 2026-07-16, RM-07)

Status: **restored** under RM-BL-B1 (pending Lead commit). Prior “deleted” list retained for audit trail.

- `it scopes dashboard to assigned dormitories only` (`DormitoryManagerDashboardTest`) — restored
- `it reports correct unit and occupancy counts for assigned dormitory` (`DormitoryManagerDashboardTest`) — restored
- `it scopes unit dashboard to assigned rooms only` (`DormitoryUnitManagerDashboardTest`) — restored
- `it counts occupied reserved and vacant independently` (`DormitoryUnitManagerDashboardTest`) — restored
- `it renders zero-bed assigned rooms with zero counts` (`DormitoryUnitManagerDashboardTest`) — restored
- Helpers restored: `assignManagerToDormitory`, `assignUnitManagerToRoom`, `seedDormitoryHierarchyForDashboard`, `seedUnitManagerHierarchy`, `seedBedsForRoom`
- FK suite: `tests/Feature/Modules/DormitoryAdmin/AssignmentForeignKeyTest.php` (RM-06)

## BL-04 — IdentityRoleGuard ownership / Shared Kernel migration

- Status: **Mitigated / Delivered**
- Origin: DG-03 Notes (reopen trigger: second consumer of Identity Helper)
- Trigger: Phase F2 (employee-auth-ui) is the second consumer.
- Decision (Lead): Migrate IdentityRoleGuard to Shared Kernel.
- Evidence: W-06 IMPLEMENTED — `app/Shared/Auth/IdentityRoleGuard.php`; DG-03 RESOLVED; dormitory-admin dashboards + `EnsureIdentityRole` consume Shared Kernel.
- Residual: Formal L7/L8 for employee-auth-ui still tracked as W-07/W-08 (process), not BL-04 ownership drift.
- Owner: Lead
- Cross-refs: DG-03, employee-auth-ui/work-breakdown.md (W-06)

## F-W07-02 — Establish-fail logout parity (F2 employee-auth-ui)

- Status: **Accepted + verified (W-08)**
- Origin: W-07 security review (`docs/features/employee-auth-ui/w07-security-review-report.md`)
- Decision (Lead): Risk accept Establish-fail cleanup via default logout only (no F-W07-02 code change)
- Verification: W08-C-01 / W08-C-02 PASS (`tests/Feature/Auth/EmployeeLoginW08Test.php`); W-08 CLOSED
- Owner: Lead
- Cross-refs: W-07 CLOSED, W-08 CLOSED, `docs/features/employee-auth-ui/work-breakdown.md`

## TD-FE-001 — PHPUnit Vite manifest stub persists into browser runtime asset path

- **Title:** PHPUnit Vite manifest stub persists into browser runtime asset path
- **Classification:** Test/runtime isolation debt
- **Severity:** Medium (developer workflow only; no production/CI impact)
- **Status:** **Accepted technical debt** (Lead — do not implement fix now)
- **Root cause:** Create-if-absent + persist: `tests/TestCase.php::ensureViteManifestForTests()` creates a stub `public/build/manifest.json` when missing; duplicated `beforeEach` writers in UI flow tests can leave the same stub. Stub does **not** overwrite an existing valid manifest. After tests, local browser may load the stub and fail `assets/app.css` (404 / unstyled UI).
- **Workaround:** Run `npm run build` after test runs if local styling breaks.
- **Remediation direction (future, not now):** Tests must not write under `public/build`; isolate test manifest handling away from the browser asset path.
- **Re-evaluation triggers:** Multiple developers onboarded · incident recurrence · pre-release hardening
- **Owner:** Tech Lead
