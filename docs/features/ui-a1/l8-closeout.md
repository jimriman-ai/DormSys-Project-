# L8 Closeout — UI-A1 (Auth Layout / Identity Guard Integration)

| Field | Value |
|-------|--------|
| **Feature / Catalog ID** | **UI-A1** |
| **Spec** | SPEC-UI-A1-L3-v2 |
| **Decision boundary** | Auth layout / identity guard integration (`dormitory-admin-ui`) |
| **Date** | 1405/04/24 (2026-07-15) |
| **Closeout role** | L8 — docs-only; no implementation |
| **Application code modified in L8?** | **No** |
| **Commit in L8?** | **No** |

---

## 1. Feature status

| Field | Value |
|-------|--------|
| **UI-A1 status** | **COMPLETE — READY FOR COMMIT AND CLOSURE REVIEW** |
| **Upstream gates** | L3 AUTHORIZED → L5 AUTH GATE **PASS** → L6 EXECUTED → L7 VERIFICATION **PASS** → L8 CLOSEOUT (this artifact) |
| **Program F3** | Remains **ACTIVE — Sprint A** (UI-M1 / UI-M2 still PENDING) |
| **Canonical status mirror** | `docs/governance/roadmap.md` § F3 Catalog — UI-A1 row updated to COMPLETE (closeout sync) |

UI-A1 is ready for Lead commit / PR / closure review. This closeout does **not** create a git commit.

---

## 2. L6 implementation summary

| Item | Outcome |
|------|---------|
| **FR-6** | **Option A** — split routes: per-role `identity.role:*` under `auth:identity` (no route-shape change for dormitory-admin) |
| **L6-R1** | **Amend** — web `POST /logout` middleware = `auth:api,identity` (extracted from app-shell `auth:api` + mutation/audit group) |
| **Layout** | Logout form + `@csrf` + optional identity label via `auth('identity')` only (DEC-UIA1-G5 / FR-3) |
| **Guard registration** | Unchanged (verify-only) — alias `identity.role` → `EnsureIdentityRole` → `IdentityRoleGuard` |
| **Migrations** | None |

Evidence: L6 execute working tree + `docs/features/ui-a1/l7-verification.md` §1 / §3 / §4.

---

## 3. L7 verification result

| Source | Result |
|--------|--------|
| Artifact | `docs/features/ui-a1/l7-verification.md` |
| Overall | **L7 VERIFICATION: PASS** |
| Scope | PASS |
| No `UserModel` FQCN import in layout/admin UI surface | PASS |
| L6-R1 Amend logout | PASS |
| FR-6 Option A routes unchanged | PASS |
| Tests | PASS (see §5) |

---

## 4. Changed files (UI-A1 deliverable set)

| Path | Role |
|------|------|
| `resources/views/components/layouts/dormitory-admin.blade.php` | Auth shell: logout + CSRF; `auth('identity')` label |
| `routes/web.php` | L6-R1 Amend logout middleware only |
| `tests/Feature/Modules/DormitoryAdmin/UiA1AuthLayoutTest.php` | Feature tests (new) |
| `docs/features/ui-a1/l7-verification.md` | L7 evidence |
| `docs/features/ui-a1/l8-closeout.md` | This closeout |
| `docs/governance/roadmap.md` | Catalog status sync only (UI-A1 COMPLETE) |

**Not changed by UI-A1:** `config/auth.php`, `bootstrap/app.php`, `IdentityRoleGuard`, `EnsureIdentityRole`, Livewire dashboard **data** logic, migrations.

---

## 5. Test evidence

Commands (from L7):

```text
php artisan test --filter=UiA1AuthLayoutTest
php artisan test tests/Feature/Modules/DormitoryAdmin/DormitoryManagerDashboardTest.php tests/Feature/Modules/DormitoryAdmin/DormitoryUnitManagerDashboardTest.php tests/Feature/Modules/DormitoryAdmin/DormitoryAdminSecurityRemediationTest.php
```

| Suite | Result |
|-------|--------|
| `UiA1AuthLayoutTest` | **3 passed**, 11 assertions |
| Manager + Unit + Security remediation | **14 passed**, 31 assertions |
| Exit code | **0** |

UiA1 coverage: layout logout+CSRF; identity-only logout success; absolute guest logout redirect.

---

## 6. Governance decisions (locked)

| ID | Decision | Effect |
|----|----------|--------|
| **FR-6** | **Option A** | Split per-route role middleware; shared layout; role page split remains UI-M1/M2 concern |
| **L6-R1** | **Amend** | Logout accepts `api` **or** `identity` principal — closes identity-only UX/security gap |
| **DEC-UIA1-G5** | Auth helper / `Authenticatable` access | No direct `UserModel` FQCN import in Blade/Livewire layout surface |
| **DGAP-11** | CLOSED — RESOLVED (prior) | Catalog includes UI-A1; F3 Sprint A |

---

## 7. Scope exclusions (explicit)

UI-A1 closeout does **not** include:

| Excluded | Owner / later item |
|----------|-------------------|
| UI-M1 Manager Dashboard wire data | BL-B1-01 blocked; catalog PENDING |
| UI-M2 Unit-Manager Dashboard wire data | BL-B1-01 blocked; catalog PENDING |
| Dashboard occupancy/assignment widgets or queries | Out of UI-A1 |
| Assignment schema / UI-A2 | Separate Lead proposal if needed |
| New migrations | C-1 |
| Architecture / dual-guard redesign | DGAP-10 CLOSED — intentional; UI-A1 consumes existing |
| Commit / PR merge | Lead closure review |

---

## 8. Closeout checklist

- [x] L3 authorized (Lead)
- [x] L5 AUTH GATE PASS
- [x] L6 EXECUTED (Option A + Amend)
- [x] L7 VERIFICATION PASS
- [x] L8 closeout report written
- [x] Roadmap UI-A1 status synced to COMPLETE
- [ ] Git commit (Lead)
- [ ] Closure review / merge (Lead)

---

## Final state

**UI-A1 is COMPLETE and ready for commit and closure review.**

No application code was modified in L8. No commit was created.
