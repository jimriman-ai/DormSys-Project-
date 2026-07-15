# L9 Merge Checklist — dormitory-admin-ui
**Gate:** merge ⇐ تمام موارد ✅ + sign-off صریح Lead (D-G-14)  
**Branch:** `security/g-phase-dormitory-admin-ui`  
**Base:** `origin/011-reporting-projections` (`origin/main` absent)  
**Assessed:** 2026-07-15  
**Overall:** **NOT READY FOR MERGE** — blockers A2, D1, E3 (and A1/E2 open)

**L9-R:** REQUEST CHANGES (see § E1)

---

## A — تطابق با Canonical Decisions

- [ ] **A1.** تغییرات فقط در scope dormitory-admin-ui (D-G-10) — هیچ فایل خارج از boundary  
  - **FAIL.** Diff includes Identity/`UserModel`, `config/auth.php`, Auth session login/logout, `App\Support\Auth\IdentityRoleGuard`, companion Identity test — not dormitory-admin-ui-only.  
  - *Mitigation path:* Lead accepts foundational auth wiring as Phase G surface under D-G-12/D-G-16, **or** split Identity dual-guard into separate PR.

- [ ] **A2.** `IdentityRoleGuard` داخل namespace ماژول dormitory (D-G-11)  
  - **FAIL.** `app/Support/Auth/IdentityRoleGuard.php` → `namespace App\Support\Auth` (L5).  
  - *Fix:* move to e.g. `App\Modules\DormitoryAdmin\...` (or `App\Modules\Dormitory\...`) before merge; BL-04 remains promotion-to-kernel only.

- [x] **A3.** `UserModel.$guard_name` = `['web','identity']` (D-G-12)  
  - **PASS.** Evidence: `UserModel.php:48` (`protected array $guard_name = ['web', 'identity'];`). Checklist L24 is stale (that line is `@property`).

- [x] **A4.** rule صریح role-assignment (guard: identity) در seeder/docs (D-G-12)  
  - **PASS.** Seeder: `IdentityRoleSeeder.php:100-103` (`Role::findOrCreate(..., 'identity')` + comment). L5 auth record §2 / §5. Security doc remediated notes SEC-G-01 identity guard.

---

## B — Security Remediation (G-E)

- [x] **B1.** `EnsureIdentityRole` guard `identity` را صریحاً چک می‌کند (SEC-G-01)  
  - **PASS.** `EnsureIdentityRole.php:22` + `IdentityRoleGuard.php:29` (`where('guard_name', 'identity')`).

- [x] **B2.** alias در `bootstrap/app.php`؛ Spatie `role:` از routes حذف  
  - **PASS.** `bootstrap/app.php:57` (`identity.role`). Routes: `identity.role:dormitory-manager|unit-manager` only (`routes/web.php:24,28`); no bare Spatie `role:`.

- [x] **B3.** `assertIdentityRole()` در ابتدای `render()` هر دو component (SEC-G-02)  
  - **PASS.** `DormitoryManagerDashboard.php:20`; `DormitoryUnitManagerDashboard.php:20`.

- [x] **B4.** هیچ public collection قابل hydration نیست (SEC-G-03)  
  - **PASS.** Locals + `->with()`; reflection test in remediation suite; no `public $dormitories|$rooms`.

- [x] **B5.** SEC-G-04 in `docs/security/dormitory-admin-ui.md` with Owner=Tech Lead, 6‑month cadence (D-G-13, D-G-15)  
  - **PASS.** `docs/security/dormitory-admin-ui.md:19-21`.

---

## C — Invariants

- [x] **C1.** تمام FKها `restrictOnDelete()` — هیچ cascade  
  - **PASS.** Both Phase G assignment migrations use `restrictOnDelete()` only.

- [x] **C2.** FK types UUID match PK  
  - **PASS.** `foreignUuid` → `identity_users` / `dormitories` / `dormitory_rooms`.

- [x] **C3.** Dashboardها فقط DB query builder  
  - **PASS.** `DB::table(...)` aggregations; no Eloquent dormitory models in components.

- [x] **C4.** occupancy فقط `vacant|reserved|occupied`  
  - **PASS.** Unit dashboard counts all three; manager uses `occupied` for occupancy math.

---

## D — Tests & Quality

- [ ] **D1.** کل test suite سبز  
  - **FAIL.** Last full run on branch: **1817 passed / 19 errors / 4 skipped** (Spatie permission seed / unique / pivot FK cluster). DormitoryAdmin filter: **16/19** (3 errors, same seed symptom).  
  - *Gate:* suite must be green before Lead sign-off.

- [x] **D2.** تست‌های guard-mismatch  
  - **PASS (coverage).** `DormitoryAdminSecurityRemediationTest.php:45-68` (web role → 403). Runtime green for these cases in last filter run; happy-path blocked by seeder flake.

- [x] **D3.** تست revocation در Livewire subsequent request  
  - **PASS (coverage).** Same file `:87-109`, `:112-134` — load → detach → `$refresh` → 403.

---

## E — Closure (D-G-16)

- [x] **E1.** خروجی L9-R ضمیمه  
  - **PASS (artifact).** L9-R verdict: **REQUEST CHANGES**.  
  - Findings disposition under L5:
    | Finding | Disposition |
    |---------|-------------|
    | Migrations/FK in PR | **Accept under invariants C1–C2 / Phase G surface** (not cascade) |
    | Dual-guard UserModel | **Authorized D-G-12**; removal → **BL-02** |
    | F2 / global guard-confusion | **Backlog BL-01** (D-G-10) |
    | Suite red | **Must fix** (engineering gate) |
    | `IdentityRoleGuard` in `Support` | **Must fix for A2 / D-G-11** (or Lead supersede) |
    | Broad file set | Lead accept under D-G-12/16 **or** trim (A1) |

- [ ] **E2.** Backlog items BL-01..04 در tracker ثبت شده  
  - **FAIL / incomplete.** Listed in L5 record (`.specify/memory/dormitory-admin-ui-authorization.md` §4) and referenced in `open-decisions.md`; **no dedicated program tracker / issues** found for BL-01..04.

- [ ] **E3.** **Sign-off صریح Lead** ← merge ← Phase G CLOSED  
  - **OPEN.** No Lead merge approval recorded.

---

## Gate summary

| Section | Status |
|---------|--------|
| A Canonical | **FAIL** (A1 open, **A2 blocker**) |
| B Security G-E | **PASS** |
| C Invariants | **PASS** |
| D Tests | **FAIL** (D1) |
| E Closure | **FAIL** (E2, E3; E1 = REQUEST CHANGES attached) |

**Merge authorized?** **NO** — until A2 (+ optional A1 Lead exception), D1 green, E2 tracker, and **E3 Lead sign-off**.
