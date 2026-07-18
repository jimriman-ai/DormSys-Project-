# L9 Merge Checklist — dormitory-admin-ui (UI-M1)

**Gate:** merge ⇐ all items PASS / N-A / WAIVED + Lead AUTH-011 Band 2 (2026-07-16)  
**Branch:** `release/f2-employee-auth-ui-l9` @ HEAD (post W1 refresh)  
**Base / merge target:** `011-reporting-projections` (`origin/main` absent on remote — per prior L9 record)  
**Assessed:** 2026-07-16 (AUTH-011 Band 2 refresh — supersedes 2026-07-15)  
**Overall:** **NOT READY FOR MERGE** — G3 blocked: `bootstrap/app.php` conflict vs `011-reporting-projections` (dry-run 2026-07-16). Checklist items PASS/WAIVED. **VCS (commit/merge) is Lead-owned** — Cursor scope = docs + report only (AUTH-011 Band 2 Scope Correction, 2026-07-18).

**L9-R:** Checklist APPROVED (G1) pending Lead conflict resolve + merge (G3)

---

## Waiver W1-A1 (HD-06 / D1 — verbatim)

> WAIVER W1-A1 (Lead, 2026-07-16): The presence of UI-A1 artifacts in the UI-M1 branch is intentional and accepted for the L9 merge. UI-A1 items on the L9 checklist are marked WAIVED (not PASS). This waiver is valid ONLY when attached to a checklist refreshed in this session (per HD-06: stale checklist = invalid evidence).

---

## A — تطابق با Canonical Decisions

- [x] **A1.** تغییرات فقط در scope dormitory-admin-ui (D-G-10)  
  - **WAIVED (W1-A1).** Diff includes Identity/auth/UI-A1 foundational wiring beyond strict dormitory-admin-ui boundary — **intentional and accepted** per Lead D1 / HD-06. Evidence: `routes/web.php`, `app/Shared/Auth/IdentityRoleGuard.php`, `config/auth.php`, employee-auth-ui paths.

- [x] **A2.** `IdentityRoleGuard` namespace (D-G-11)  
  - **WAIVED (DG-03 supersede).** Guard at `app/Shared/Auth/IdentityRoleGuard.php` (`App\Shared\Auth`) — **BL-04 Delivered**, DG-03 RESOLVED (Shared Kernel). D-G-11 dormitory-module path superseded. Evidence: `risk-register.md` § BL-04; `open-decisions.md` DG-03.

- [x] **A3.** `UserModel.$guard_name` = `['web','identity']` (D-G-12)  
  - **PASS.** `UserModel.php:48` — `protected array $guard_name = ['web', 'identity'];`

- [x] **A4.** rule صریح role-assignment (guard: identity) در seeder/docs (D-G-12)  
  - **PASS.** `IdentityRoleSeeder.php` — `Role::findOrCreate(..., 'identity')`; `docs/security/dormitory-admin-ui.md`.

---

## B — Security Remediation (G-E)

- [x] **B1.** `EnsureIdentityRole` guard `identity` را صریحاً چک می‌کند (SEC-G-01)  
  - **PASS.** `EnsureIdentityRole.php` + `IdentityRoleGuard.php` (`where('guard_name', 'identity')`).

- [x] **B2.** alias در `bootstrap/app.php`؛ Spatie `role:` از routes حذف  
  - **PASS.** `bootstrap/app.php:81` (`identity.role`); routes `identity.role:dormitory-manager|unit-manager` only.

- [x] **B3.** `assertIdentityRole()` در ابتدای `render()` هر دو component (SEC-G-02)  
  - **PASS.** `DormitoryManagerDashboard.php:19`; `DormitoryUnitManagerDashboard.php:19`.

- [x] **B4.** هیچ public collection قابل hydration نیست (SEC-G-03)  
  - **PASS.** Locals + `->with()`; no `public $dormitories|$rooms`; `DormitoryAdminSecurityRemediationTest.php`.

- [x] **B5.** SEC-G-04 in `docs/security/dormitory-admin-ui.md` (D-G-13, D-G-15)  
  - **PASS.** Owner Tech Lead; 6-month cadence at `:19-21`.

---

## C — Invariants

- [x] **C1.** تمام FKها `restrictOnDelete()` — هیچ cascade  
  - **PASS.** `2026_07_16_000001/000002` assignment migrations — `restrictOnDelete()` only.

- [x] **C2.** FK types UUID match PK  
  - **PASS.** `foreignUuid` → `identity_users` / `dormitories` / `dormitory_rooms`.

- [x] **C3.** Dashboardها فقط DB query builder  
  - **PASS.** `DB::table(...)` in both dashboard components.

- [x] **C4.** occupancy فقط `vacant|reserved|occupied`  
  - **PASS.** Unit dashboard counts all three; manager uses `occupied`.

---

## D — Tests & Quality

- [x] **D1.** کل test suite سبز  
  - **PASS.** **1888 passed**, 0 failures, exit 0 — `storage/logs/auth011-band2-junit.xml` (Lead-recorded; `DB_HOST=pgsql REDIS_HOST=redis`). No deadlock in `AuditHistoryUiFlowTest` / `ProjectionRefreshMaterializationTest`.

- [x] **D2.** تست‌های guard-mismatch  
  - **PASS.** `DormitoryAdminSecurityRemediationTest.php:45-68`.

- [x] **D3.** تست revocation در Livewire subsequent request  
  - **PASS.** Same file `:87-134`.

---

## E — Closure (D-G-16)

- [x] **E1.** خروجی L9-R ضمیمه  
  - **PASS.** This refreshed checklist + AUTH-011 Band 2 readiness report.

- [x] **E2.** Backlog items BL-01..04 در tracker ثبت شده  
  - **PASS.** `docs/governance/risk-register.md` § L9 Backlog Tracker (BL-01…04) — AUTH-011 Band 2 W1.

- [x] **E3.** **Sign-off صریح Lead** ← merge  
  - **PASS.** Lead AUTH-011 Band 2 + Phase 2 conditional merge authorization (2026-07-16).

---

## Gate summary

| Section | Status |
|---------|--------|
| A Canonical | **PASS** (A1/A2 WAIVED) |
| B Security G-E | **PASS** |
| C Invariants | **PASS** |
| D Tests | **PASS** (D1 green) |
| E Closure | **PASS** |

**Merge authorized?** **NO** until Lead resolves G3 (`bootstrap/app.php`). Cursor does not commit or merge.
