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
| F2 | Auth: Employee UI/Auth broad scope | **PARTIAL** (boundary work-items W-01…W-08 CLOSED — `docs/features/employee-auth-ui/work-breakdown.md:14`; F-W07-04 **CARRIED FORWARD → F3 Sprint B** — `open-decisions.md` HD-05A / AUTH-013). **No** next UI slug / Spec04 Auth authorized from F2 closeout. (reconciled 2026-07-15, ref: DGAP-12) |
| G | UI: dormitory-admin-ui | **PARTIAL** (BL-B1-01 **RESOLVED (pending Lead commit)** — `docs/governance/risk-register.md:13`; L9 checklist NOT READY — `.specify/governance/l9-merge-checklist-dormitory-admin-ui.md:6-8`). |
| F3 | Dormitory Admin UI productization | **ACTIVE — Sprint A** (catalog AUTHORIZED Lead Option A, 2026-07-15). Canonical IDs: **UI-M1, UI-M2, UI-A1** (supersedes historical UI-1…UI-7 claim). DGAP-11 CLOSED — see § F3 Catalog. |

### F3 — Catalog (Sprint A)

**Entry:** AUTHORIZED — Option A (scope corrected), Lead, 2026-07-15.  
**Supersedes:** undelivered UI-1…UI-7 / L6.1–L6.7 claim under prior DGAP-11 Option A wording.  
**Artifact authority:** this section of `docs/governance/roadmap.md` (DGAP-11 closure evidence).

| ID | Title / Scope | Status | Dependency / Evidence |
|----|---------------|--------|------------------------|
| **UI-M1** | Manager Dashboard — wire data (`/dormitory-admin`, manager role) | **UNBLOCKED — READY FOR L3/L6** | BL-B1-01 **RESOLVED (pending Lead commit)**. Shell: `routes/web.php:28-30`; wired `DormitoryManagerDashboard.php` (assignment-scoped aggregate); Blade `resources/views/livewire/dormitory-admin/dormitory-manager-dashboard.blade.php`. |
| **UI-M2** | Unit-Manager Dashboard — wire data (`/dormitory-admin/unit`) | **UNBLOCKED — READY FOR L3/L6** | BL-B1-01 **RESOLVED (pending Lead commit)**. Shell: `routes/web.php:32-34`; wired `DormitoryUnitManagerDashboard.php`; Blade `resources/views/livewire/dormitory-admin/dormitory-unit-manager-dashboard.blade.php`. |
| **UI-A1** | Auth layout / identity guard integration (`IdentityRoleGuard`, dual-guard) | **COMPLETE — READY FOR COMMIT AND CLOSURE REVIEW** (L8 2026-07-15). Evidence: `docs/features/ui-a1/l7-verification.md` PASS; `docs/features/ui-a1/l8-closeout.md`. L6: Option A routes; L6-R1 Amend `auth:api,identity` logout; layout logout form. | Shell: `resources/views/components/layouts/dormitory-admin.blade.php`; logout: `routes/web.php:37-40`; tests: `tests/Feature/Modules/DormitoryAdmin/UiA1AuthLayoutTest.php`. |

**Out of this catalog (do not conflate):** Assignment schema / assignment UI is **not** UI-A1. If required later, propose separately as **UI-A2** (Lead note, 2026-07-15).

**Sprint A first work item posture:** Catalog defined; implementation / L3 per item requires separate Lead authorization (docs-only close of DGAP-11 does **not** authorize PHP/migrations).

---

## F3 Execution Waves (AUTH-013 — sequencing only)

> **Authority:** Lead AUTH-013 (2026-07-16). Sequencing record only — does not authorize code, merge, or spec content.
> **Logic:** Decisions recorded → merge → doc batch → new work. Doc rewrites before merge would stale again post-merge.

| Wave | Content | Gaps Resolved | Gate |
|------|---------|---------------|------|
| **W0 — Decision Recording** (docs-only) | Record HD-01…07 in `open-decisions.md` + disposition annotations in `spec-catalog.md` | GAP-GOV-01 (waiver path), GAP-SPEC-06/11 (→ ACCEPTED-EXCEPTION), GAP-UI-M2-01 (→ scheduled W3) | **AUTH-013** (this prompt) |
| **W1 — L9 Merge** | Refresh checklist → disposition two unstaged files → readiness report → merge + SHA | GAP-DOC-04, GAP-GOV-01, GAP-GOV-02, close GAP-GOV-03 (minimal BL-01…04 tracker) | **AUTH-011 Band 2** |
| **W2 — Hygiene & Doc-lag Batch** | N-11 (S-4 grep CI + dedicated test DB note) + all doc-lags: GAP-DOC-01/02/03, GAP-UI-M1-01, spec02/spec05 closeout (HD-07A), GAP-N11-01 | All doc-lag tags in `project-state.md` §8 | **AUTH-011 Band 3** (scope extended per HD-07) |
| **W3 — UI-M2 L3** | `docs/features/ui-m2/l3-spec.md` per UI-M1 pattern | GAP-UI-M2-01 executed | **AUTH-011 Band 4** |
| **P — Parked Lane** (no work) | DGAP-08/03/05/06, SGAP-07, spec06/11 authority debt, Workflow, F-W07-04 | — each with explicit re-entry trigger in `open-decisions.md` | Future Lead decision |

**W2 acceptance criterion:** After W2, `project-state.md` §8 contains only decision-pending rows with explicit triggers — **zero open doc-lag rows**.

**Current wave:** W0 **COMPLETE** → **W1 next** (AUTH-011 Band 2).

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
| AUTH-013 waves | `docs/governance/roadmap.md` § F3 Execution Waves |
| Project state snapshot | `docs/governance/project-state.md` |

---

## Changelog

| تاریخ | تغییر | توسط |
|-------|-------|------|
| 1405/04/25 (2026/07/16) | **AUTH-013 W0:** Four-wave + Parked Lane sequencing recorded; W0 complete / W1 next. HD-01…07 decisions cite AUTH-013. | Agent (Lead AUTH-013) |
| 1405/04/25 (2026/07/16) | **BL-B1-01 / RM-BL-B1:** UI-M1/UI-M2 → UNBLOCKED — READY FOR L3/L6; assignment schema restored; DGAP-09 RE-FROZEN | Agent (Lead AUTHORIZE ALL) |
| 1405/04/24 (2026/07/15) | **UI-A1 L8 closeout:** status → COMPLETE — READY FOR COMMIT AND CLOSURE REVIEW (`docs/features/ui-a1/l8-closeout.md`); L7 PASS | Agent (L8 docs) |
| 1405/04/24 (2026/07/15) | **F3 Sprint A entry / DGAP-11 close:** Canonical catalog UI-M1, UI-M2, UI-A1 (PENDING); supersedes UI-1…UI-7 claim; F3 ACTIVE — Sprint A | Agent (Lead AUTHORIZED Option A) |
| 1405/04/24 (2026/07/15) | **DGAP-12 reconciliation:** F2 → PARTIAL (F-W07-04 open); G → PARTIAL (BL-B1-01 + L9 NOT READY); F3 stub PLANNED (DGAP-11 reopened — UI-1…UI-7 never delivered); L9 checklist cross-ref added | Agent (Lead-supervised docs) |
| 1405/04/24 | F2 `employee-auth-ui` W-01…W-08 CLOSED (Lead W-07/W-08 acceptance); UI entry not authorized | Agent (Lead closeout) |
| 1405/04/24 | DOC hygiene: W-02 CLOSED (DGAP-07 A); W-07 IN EXECUTION (scope YES+AMEND) | Agent (Lead-authorized batch) |
| 1405/04/24 | Option B reconciliation: F2 ACTIVE notes W-01/W-06 impl accepted; W-07/W-08 remain open | Lead |
| 1405/04/24 | L3 spec authored for W-01/W-03; W-03 closed without action (DG-03/Option A) | Lead |
| 1405/04/24 | Initial roadmap created; Phase F split into F1/F2 per DG-02; program-level scope declared | Lead |
