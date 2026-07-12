# PHP Artisan Test Failure Inventory

**Date:** 2026-07-12  
**Command:** `php -d memory_limit=512M artisan test`  
**Initial state:** 21 failed, 11 risky, 4 skipped, 1740 passed (user report); captured JSON run: 21 failed, 1751 passed, 4957 assertions, `risky: 11` (count only — Pest JSON did not enumerate risky names)

**Evidence source:** `docs/debug/pest-summary.json` (from full suite run)

---

## 1. Test failure inventory

### Group A — Shared layout 500 (`PermissionDoesNotExist: audit.read`) — 19 tests

| # | Test | File | Error | Expected vs actual | Stack root (app) | Module | DB/factory |
| - | ---- | ---- | ----- | ------------------ | ---------------- | ------ | ---------- |
| 1 | `employee hub ui access → it renders the authenticated employee hub page` | `tests/Feature/Modules/Employee/EmployeeHubUiFlowTest.php` | `PermissionDoesNotExist` | 200 vs 500 | `SpatieAuditPermissionReadAdapter::principalHasAuditReadPermission` via `LayoutNavAuditLinkComposer` | Identity → Audit UI layout | `permissions` missing `audit.read`; UI fixtures do not seed `IdentityRoleSeeder` |
| 2 | `employee hub ui access → it places کارکنان nav immediately after اعلان‌ها` | same | same | same | same | same | same |
| 3–13 | Notification inbox UI access + layout navigation (11 cases) | `tests/Feature/Modules/Notification/NotificationInboxUiFlowTest.php` | same | same | same | same | same |
| 14 | `request list detail navigation → employee can navigate from owned request list row…` | `tests/Feature/Modules/Request/RequestListDetailNavigationUiFlowTest.php` | same | same | same | same | same |
| 15 | `request show ui flow → employee can open owned request detail` | `tests/Feature/Modules/Request/RequestShowUiFlowTest.php` | same | same | same | same | same |
| 16–19 | Request UI access/flows (4 cases) | `tests/Feature/Modules/Request/RequestUiFlowTest.php` | same | same | same | same | same |

**Exception message (shared):**  
`Spatie\Permission\Exceptions\PermissionDoesNotExist: There is no permission named \`audit.read\` for guard \`web\`.`

**Application path:**  
`LayoutNavAuditLinkComposer` → `AuditPermissionReadPort::principalHasAuditReadPermission` → `SpatieAuditPermissionReadAdapter` → `$user->hasPermissionTo('audit.read')` (throws when permission row absent).

**Related:** `resources/views/components/layouts/app.blade.php` (`$show_audit_nav`); permission defined in `IdentityRoleSeeder::PERMISSION_AUDIT_READ`.

---

### Group B — Module boundary arch — 2 tests

| # | Test | File | Error | Module | Related code |
| - | ---- | ---- | ----- | ------ | ------------ |
| 20 | `Identity infrastructure is isolated from App\Modules\Audit` | `tests/Architecture/ModuleBoundaryTest.php` | Pest arch: Identity Infrastructure must not use `App\Modules\Audit\*` | Identity | `SpatieAuditPermissionReadAdapter.php`, `IdentityServiceProvider.php` |
| 21 | `Reporting infrastructure is isolated from App\Modules\Audit` | same | Pest arch: Reporting Infrastructure must not use `App\Modules\Audit\*` | Reporting | `AuditHistorySourceReadAdapter.php`, `ReportingArchiveVisibilityAdapter.php`, `ActorActivitySummaryRepository.php`, `AuditWindowAggregateRepository.php`, `CorrelationProjectionEntryRepository.php` |

---

## 2. Classification (Phase 2 — before code changes)

| Group | Classification | Rationale |
| ----- | -------------- | --------- |
| A (19 UI) | **1) Production code regression** | Layout permission read uses `hasPermissionTo`, which throws when the permission catalog is empty. A read port must answer boolean capability, not crash shared layouts. Missing seed in non-audit UI tests is a fixture gap, but production must remain safe when permission is absent. |
| B Identity | **1) Production / architecture placement** (+ **5** governance edge) | Cross-module port impl lives under Identity Infrastructure (legacy). Approved policy requires `app/Integrations/` + composition root. |
| B Reporting | **1) Production / architecture placement** | Cross-module adapters under Reporting Infrastructure + Infrastructure repositories typed on Audit DTOs violate ModuleBoundary. Application layer may use Audit DTOs; Infrastructure must not. |
| Risky (11) | Pending identification after failure fix | Pest JSON only returned integer `risky: 11`; names to be captured with `--display-all-issues` once failures clear. |

**Not classified as invalid test expectations:** UI tests correctly expect HTTP 200 for authenticated layout pages. Architecture tests correctly enforce isolation; remediation is production placement / DTO ownership, not assertion weakening.

---

## 3. Root cause hypotheses

1. **Audit UI nav composer** calls permission port on every authenticated layout render; adapter throws if `audit.read` is not seeded → cascades across Employee/Notification/Request UI flows.
2. **Identity/Reporting → Audit** edges remain in module Infrastructure despite integration-layer policy / known-exception tracking; ModuleBoundary does not exempt them.

---

## 4. Proposed remediation (pre-implementation)

| Group | Fix | Risk |
| ----- | --- | ---- |
| A | Use Spatie `checkPermissionTo` (returns false if permission missing) in permission read adapter/bridge | Low — preserves deny semantics; no auth bypass |
| B Identity | Move adapter to `app/Integrations/`, bind in `IntegrationServiceProvider`, remove legacy registry entry | Low — debt closure |
| B Reporting | (1) Move Audit bridges to Integrations; (2) introduce Reporting-owned `ProjectionSourceItemDto` for write ports/repos so Infrastructure does not import Audit DTOs; map in Application materializers | Medium — touches Reporting write ports/materializers; behavior unchanged if mapping is field-complete |
| Risky | Investigate after green failures | TBD |

---

## 5. Explicit non-actions

- Do not skip/disable failing tests
- Do not seed `IdentityRoleSeeder` in every UI test as the sole fix (would leave production throw path)
- Do not expand legacy exception registry
- Do not weaken ModuleBoundary by ignoring foreign Application imports without production boundary closure
