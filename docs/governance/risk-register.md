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
| BL-B1-01 | **B1 deferred — schema gap if dependent feature ships** (assignment tables removed from Phase G tip; dashboards no longer query them) | High (assignment/occupancy UI cannot ship) | High if Stage-3 / assignment UI started without tables | Tech Lead | Reopen when assignment schema lands (`foundation/dormitory-admin-tables` or Phase H). Deleted B1 behavior tests logged below. | On Phase H start or consumer needing assignments | Open / deferred |
| G-E-R-CACHE | Spatie permission Redis cache + RefreshDatabase pollution under dual-guard | Medium (flaky PermissionDoesNotExist) | Medium in CI | Tech Lead | Test env `PERMISSION_CACHE_STORE=array`; seeder mirrors permissions on `web`+`identity` | After CI flake | Mitigated (test) |

## BL-B1-01 — Deleted tests (assignment / B1 behavior)

Reopen trigger: restore assignment schema + reintroduce these tests (or successors).

- `it scopes dashboard to assigned dormitories only` (`DormitoryManagerDashboardTest`)
- `it reports correct unit and occupancy counts for assigned dormitory` (`DormitoryManagerDashboardTest`)
- `it scopes unit dashboard to assigned rooms only` (`DormitoryUnitManagerDashboardTest`)
- `it counts occupied reserved and vacant independently` (`DormitoryUnitManagerDashboardTest`)
- `it renders zero-bed assigned rooms with zero counts` (`DormitoryUnitManagerDashboardTest`)
- Helpers removed: `assignManagerToDormitory`, `assignUnitManagerToRoom`, `seedDormitoryHierarchyForDashboard` (assignment path), `seedUnitManagerHierarchy` / `seedBedsForRoom` (assignment path)

## BL-04 — IdentityRoleGuard ownership / Shared Kernel migration

- Status: Open / Deferred-Execution
- Origin: DG-03 Notes (reopen trigger: second consumer of Identity Helper)
- Trigger: Phase F2 (employee-auth-ui) is the second consumer.
- Decision (Lead): Migrate IdentityRoleGuard to Shared Kernel. Execution deferred to F2 stage L6 (work item W-06 in employee-auth-ui work-breakdown).
- Risk if unexecuted: duplicated guard logic across two UI boundaries; drift between dormitory-admin-ui and employee-auth-ui role checks.
- Owner: Lead
- Cross-refs: DG-03, employee-auth-ui/work-breakdown.md (W-06)
