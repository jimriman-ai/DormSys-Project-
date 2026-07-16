# Program Roadmap — Canonical Phase Sequence

**Purpose:** This document canonicalizes the program-level phase sequence (A→G) and F3 dormitory-admin UI productization catalog. It is NOT an execution/feature tracker.

**Created:** 1405/04/24 | **Owner:** Lead | **Source:** DG-02

---

## Scope & Provenance

**Scope:** program-level phase sequence only. Feature-specific execution and remediation status (e.g., dormitory-admin-ui) is tracked in the respective feature artifacts, not here.

**Provenance:** some phase labels are reconstructed from governance evidence (decision records, ledgers, test baselines) rather than a single original planning document.

---

## Phase Sequence

| Phase | Title/Scope | Status |
|-------|-------------|--------|
| A | Domain Foundation (entity inventory, relationship matrix, actor/role taxonomy) | COMPLETE |
| B | Ownership & Authority (ownership matrix, authority gap register, human decision pack) | COMPLETE |
| C | Spec Gap Closure (dependency tables, canonical glossary, completed boundary specs) | COMPLETE |
| D | PEP Rebuild (PEP inventory, decomposition, readiness screening, canonical policy-level records) | COMPLETE |
| E | Exit Criteria / Domain Stabilization Exit | COMPLETE |
| F1 | Auth: employee-records | COMPLETE |
| F2 | Auth: Employee UI/Auth broad scope | **PARTIAL** (boundary work-items W-01…W-08 CLOSED — `docs/features/employee-auth-ui/work-breakdown.md:14`; F-W07-04 open/CARRIED FORWARD — target **F3 Sprint A (or later)** — `docs/features/employee-auth-ui/w07-security-review-report.md:19`; `docs/governance/open-decisions.md` § F-W07-04). **No** next UI slug / Spec04 Auth authorized from F2 closeout. (reconciled 2026-07-15, ref: DGAP-12) |
| G | UI: dormitory-admin-ui | **PARTIAL** (BL-B1-01 open — `docs/governance/risk-register.md:13`; L9 checklist NOT READY — `.specify/governance/l9-merge-checklist-dormitory-admin-ui.md:6-8`). (reconciled 2026-07-15, ref: DGAP-12) |
| F3 | Dormitory Admin UI productization | **ACTIVE — Sprint A** (catalog AUTHORIZED Lead Option A, 2026-07-15). Canonical IDs: **UI-M1, UI-M2, UI-A1** (supersedes historical UI-1…UI-7 claim). DGAP-11 CLOSED — see § F3 Catalog. |

### F3 — Catalog (Sprint A)

**Entry:** AUTHORIZED — Option A (scope corrected), Lead, 2026-07-15.  
**Supersedes:** undelivered UI-1…UI-7 / L6.1–L6.7 claim under prior DGAP-11 Option A wording.  
**Artifact authority:** this section of `docs/governance/roadmap.md` (DGAP-11 closure evidence).

| ID | Title / Scope | Status | Dependency / Evidence |
|----|---------------|--------|------------------------|
| **UI-M1** | Manager Dashboard — wire data (`/dormitory-admin`, manager role) | **PENDING** | Dep: **BL-B1-01** (`docs/governance/risk-register.md:13`). Shell: `routes/web.php:28-30`; `app/Modules/DormitoryAdmin/DormitoryManagerDashboard.php` (`$dormitories = []`); Blade `resources/views/livewire/dormitory-admin/dormitory-manager-dashboard.blade.php`. |
| **UI-M2** | Unit-Manager Dashboard — wire data (`/dormitory-admin/unit`) | **PENDING** | Dep: **BL-B1-01**. Shell: `routes/web.php:32-34`; `app/Modules/DormitoryAdmin/DormitoryUnitManagerDashboard.php` (`$rooms = []`); Blade `resources/views/livewire/dormitory-admin/dormitory-unit-manager-dashboard.blade.php`. |
| **UI-A1** | Auth layout / identity guard integration (`IdentityRoleGuard`, dual-guard) | **COMPLETE — READY FOR COMMIT AND CLOSURE REVIEW** (L8 2026-07-15). Evidence: `docs/features/ui-a1/l7-verification.md` PASS; `docs/features/ui-a1/l8-closeout.md`. L6: Option A routes; L6-R1 Amend `auth:api,identity` logout; layout logout form. | Shell: `resources/views/components/layouts/dormitory-admin.blade.php`; logout: `routes/web.php:37-40`; tests: `tests/Feature/Modules/DormitoryAdmin/UiA1AuthLayoutTest.php`. |

**Out of this catalog (do not conflate):** Assignment schema / assignment UI is **not** UI-A1. If required later, propose separately as **UI-A2** (Lead note, 2026-07-15).

**Sprint A first work item posture:** Catalog defined; implementation / L3 per item requires separate Lead authorization (docs-only close of DGAP-11 does **not** authorize PHP/migrations).

---

## Cross-References

| Ref | Path |
|-----|------|
| DG-01, DG-02, DG-03 | `docs/governance/open-decisions.md` |
| BL-B1-01 | `docs/governance/risk-register.md` |
| Terminology | `docs/governance/glossary.md` |
| Phase G L9 merge checklist (source of truth for merge readiness) | `.specify/governance/l9-merge-checklist-dormitory-admin-ui.md` (reconciled 2026-07-15, ref: DGAP-12) |
| F2 work breakdown / W-07 findings | `docs/features/employee-auth-ui/work-breakdown.md`, `docs/features/employee-auth-ui/w07-security-review-report.md` |
| DGAP-11 / DGAP-12 | `docs/governance/open-decisions.md` |

---

## Changelog

| تاریخ | تغییر | توسط |
|-------|-------|------|
| 1405/04/24 (2026/07/15) | **UI-A1 L8 closeout:** status → COMPLETE — READY FOR COMMIT AND CLOSURE REVIEW (`docs/features/ui-a1/l8-closeout.md`); L7 PASS | Agent (L8 docs) |
| 1405/04/24 (2026/07/15) | **F3 Sprint A entry / DGAP-11 close:** Canonical catalog UI-M1, UI-M2, UI-A1 (PENDING); supersedes UI-1…UI-7 claim; F3 ACTIVE — Sprint A | Agent (Lead AUTHORIZED Option A) |
| 1405/04/24 (2026/07/15) | **DGAP-12 reconciliation:** F2 → PARTIAL (F-W07-04 open); G → PARTIAL (BL-B1-01 + L9 NOT READY); F3 stub PLANNED (DGAP-11 reopened — UI-1…UI-7 never delivered); L9 checklist cross-ref added | Agent (Lead-supervised docs) |
| 1405/04/24 | F2 `employee-auth-ui` W-01…W-08 CLOSED (Lead W-07/W-08 acceptance); UI entry not authorized | Agent (Lead closeout) |
| 1405/04/24 | DOC hygiene: W-02 CLOSED (DGAP-07 A); W-07 IN EXECUTION (scope YES+AMEND) | Agent (Lead-authorized batch) |
| 1405/04/24 | Option B reconciliation: F2 ACTIVE notes W-01/W-06 impl accepted; W-07/W-08 remain open | Lead |
| 1405/04/24 | L3 spec authored for W-01/W-03; W-03 closed without action (DG-03/Option A) | Lead |
| 1405/04/24 | Initial roadmap created; Phase F split into F1/F2 per DG-02; program-level scope declared | Lead |
