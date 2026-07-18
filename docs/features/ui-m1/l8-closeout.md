# L8 Closeout — UI-M1 (Manager Dashboard Wire Data)

| Field | Value |
|-------|--------|
| **Feature / Catalog ID** | **UI-M1** |
| **Spec** | `docs/features/ui-m1/l3-spec.md` |
| **Decision boundary** | Manager dashboard assignment-scoped aggregates (`dormitory-admin-ui`) |
| **Date** | 1405/04/25 (2026-07-16) lifecycle; hygiene artifact 1405/04/27 (2026-07-18) |
| **Closeout role** | L8 — docs evidence mirror (W2 GAP-UI-M1-01) |
| **Application code modified in this closeout file?** | **No** |

---

## 1. Feature status

| Field | Value |
|-------|--------|
| **UI-M1 status** | **OBSERVED-L8-COMPLETE — L9 merge pending Lead** |
| **Upstream gates** | L3 → L6/L7/L8 delivered on branch `release/f2-employee-auth-ui-l9` |
| **Program F3** | ACTIVE — Sprint A; W1 COMPLETE (verified); W2 ACTIVE |
| **Canonical status mirror** | `docs/governance/roadmap.md` § F3 Catalog — UI-M1 row (W2 sync) |

---

## 2. Implementation summary (observed)

| Item | Outcome |
|------|---------|
| Assignment schema | Restored RM-BL-B1 — commit `369a106` |
| Manager dashboard | `DormitoryManagerDashboard.php` — assignment-scoped `DB::table` aggregates |
| Auth | `IdentityRoleGuard::assertIdentityRole` + `identity.role:dormitory-manager` |
| Tests | `DormitoryManagerDashboardTest` + security remediation suite |

---

## 3. Verification evidence

| Source | Result |
|--------|--------|
| L3 | `docs/features/ui-m1/l3-spec.md` |
| Full suite (W1-CLOSE) | **1888 passed**, exit 0 — `storage/logs/w1-close-test.log` |
| Band 2 suite | `storage/logs/auth011-band2-junit.xml` |
| L9 checklist | `.specify/governance/l9-merge-checklist-dormitory-admin-ui.md` (refreshed 2026-07-18) |
| Closeout commit (lifecycle) | `a42dc99` |

---

## 4. Scope exclusions

| Excluded | Notes |
|----------|-------|
| Merge into `011-reporting-projections` | Lead-owned (GAP-GOV-02) |
| UI-M2 L3 | W3 / AUTH-011 Band 4 |
| Assignment admin UI (UI-A2) | Not authorized |

---

## 5. Closeout checklist

- [x] L3 authored
- [x] Implementation + tests on branch
- [x] Full suite green (W1-CLOSE)
- [x] L9 checklist refreshed + W1-A1 waiver recorded
- [ ] Lead merge SHA recorded (pending)
